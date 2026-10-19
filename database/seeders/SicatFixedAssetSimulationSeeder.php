<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SicatFixedAssetSimulationSeeder extends Seeder
{
    private string $source;

    public function run(): void
    {
        $this->source = (string) config('sicat_simulation.source');
        $cutoff = (string) config('sicat_simulation.cutoff_date_2026');

        DB::transaction(function () use ($cutoff): void {
            $categories = DB::table('categories')
                ->where('name', 'like', 'Cuenta %')
                ->orderBy('name')
                ->get(['id', 'name', 'description']);
            $assets = DB::table('fixed_assets')
                ->whereIn('category_id', $categories->pluck('id'))
                ->orderBy('id')
                ->get();

            if ($categories->isEmpty() || $assets->isEmpty()) {
                throw new RuntimeException('No fixed-asset account categories or linked assets were found.');
            }

            $accounts = $this->seedAccounts($categories);
            $this->seedPeriods();
            $originalValues = $this->originalValues($assets);
            $grossValues = $this->reconcileGrossValuations($originalValues, $assets, $accounts);
            $assetProfiles = $this->seedAssetProfiles($assets, $categories, $grossValues, $accounts);
            $this->seedDepreciation($assetProfiles, $grossValues, $accounts, $cutoff);
            $this->removeOrphanSimulationDepreciationAccounts($accounts);
        });
    }

    private function seedAccounts($categories): array
    {
        $accounts = [];
        foreach ($categories as $category) {
            if (! preg_match('/^Cuenta (\d{8})$/', $category->name, $matches)) {
                continue;
            }

            $assetCode = $matches[1];
            $depreciationCode = '26'.$assetCode;
            $description = $category->description ?: $category->name;
            foreach ([
                ['code' => $assetCode, 'type' => 'fixed_asset', 'description' => $description],
                ['code' => $depreciationCode, 'type' => 'accumulated_depreciation', 'description' => 'Depreciación acumulada - '.$description],
            ] as $account) {
                DB::table('fixed_asset_accounts')->updateOrInsert(
                    ['category_id' => $category->id, 'account_type' => $account['type']],
                    [
                        'account_code' => $account['code'],
                        'category_id' => $category->id,
                        'description' => $account['description'],
                        'account_type' => $account['type'],
                        'source' => $this->source,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                $accounts[$category->id][$account['type']] = DB::table('fixed_asset_accounts')
                    ->where('account_code', $account['code'])
                    ->value('id');
            }
        }

        return $accounts;
    }

    private function seedPeriods(): void
    {
        foreach (config('sicat_simulation.periods', []) as $year => $status) {
            DB::table('fixed_asset_periods')->updateOrInsert(
                ['year' => $year],
                [
                    'status' => $status,
                    'source' => $this->source,
                    'closed_at' => $status === 'closed' ? ($year.'-12-31 23:59:59') : null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    private function removeOrphanSimulationDepreciationAccounts(array $accounts): void
    {
        $currentAccountIds = collect($accounts)
            ->pluck('accumulated_depreciation')
            ->filter()
            ->values()
            ->all();
        if ($currentAccountIds === []) {
            return;
        }

        DB::table('fixed_asset_accounts')
            ->where('source', $this->source)
            ->where('account_type', 'accumulated_depreciation')
            ->whereNotIn('id', $currentAccountIds)
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('fixed_asset_depreciation_entries')
                    ->whereColumn('fixed_asset_depreciation_entries.fixed_asset_account_id', 'fixed_asset_accounts.id');
            })
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('fixed_asset_depreciation_parameters')
                    ->whereColumn('fixed_asset_depreciation_parameters.depreciation_account_id', 'fixed_asset_accounts.id');
            })
            ->delete();
    }

    private function originalValues($assets): array
    {
        $categoryByAsset = $assets->pluck('category_id', 'id')->all();
        $originals = DB::table('fixed_asset_valuation_originals')
            ->whereIn('valuation_year', [2023, 2024, 2025, 2026])
            ->orderBy('original_valuation_id')
            ->get(['fixed_asset_id', 'valuation_year', 'amount']);
        $values = [];

        foreach ($originals as $original) {
            $categoryId = $categoryByAsset[$original->fixed_asset_id] ?? null;
            if ($categoryId === null) {
                continue;
            }

            $values[(int) $original->valuation_year][$categoryId][$original->fixed_asset_id] = $this->toCents((string) $original->amount);
        }

        return $values;
    }

    private function reconcileGrossValuations(array $originalValues, $assets, array $accounts): array
    {
        $gross = [];
        foreach ([2023, 2024] as $year) {
            $gross[$year] = $this->allocateByCategory(
                $originalValues[$year] ?? [],
                $this->toCents((string) config("sicat_simulation.note_11_references.{$year}"))
            );

            foreach ($gross[$year] as $assetId => $amountCents) {
                $categoryId = $assets->firstWhere('id', $assetId)?->category_id;
                if ($categoryId === null || ! isset($accounts[$categoryId]['fixed_asset'])) {
                    continue;
                }

                DB::table('fixed_asset_valuations')
                    ->where('fixed_asset_id', $assetId)
                    ->where('valuation_year', $year)
                    ->update([
                        'fixed_asset_account_id' => $accounts[$categoryId]['fixed_asset'],
                        'amount' => $this->fromCents($amountCents),
                        'as_of_date' => "{$year}-12-31",
                        'source' => $this->source.'_NOTE11_REFERENCE_ALLOCATION',
                        'updated_at' => now(),
                    ]);
            }
        }

        foreach ([2025, 2026] as $year) {
            foreach ($originalValues[$year] ?? [] as $categoryId => $values) {
                foreach ($values as $assetId => $amountCents) {
                    $gross[$year][$assetId] = $amountCents;
                    DB::table('fixed_asset_valuations')
                        ->where('fixed_asset_id', $assetId)
                        ->where('valuation_year', $year)
                        ->update([
                            'fixed_asset_account_id' => $accounts[$categoryId]['fixed_asset'],
                            'as_of_date' => $year === 2026 ? '2026-01-01' : '2025-12-31',
                            'source' => $this->source.($year === 2026 ? '_OPENING_BALANCE' : '_IMPORTED'),
                            'updated_at' => now(),
                        ]);
                }
            }
        }

        return $gross;
    }

    private function seedAssetProfiles($assets, $categories, array $grossValues, array $accounts): array
    {
        $categoryById = $categories->keyBy('id');
        $profiles = [];

        foreach ($assets as $asset) {
            $category = $categoryById[$asset->category_id];
            preg_match('/^Cuenta (\d{8})$/', $category->name, $matches);
            $accountCode = $matches[1] ?? '';
            $class = $this->classProfile($accountCode, $asset->id);
            $date = sprintf('%04d-%02d-01', 1985 + ($asset->id % 31), 1 + ($asset->id % 12));
            $life = $class['useful_life_years'];
            $status = match ($asset->id % 100) {
                0, 1 => 'obsoleto',
                2, 3, 4 => 'deteriorado',
                5, 6, 7, 8, 9 => 'falla',
                default => 'operativo',
            };
            $branches = ['La Paz - Sede', 'El Alto - Sede', 'La Paz - Planta externa'];

            DB::table('fixed_assets')->where('id', $asset->id)->update([
                'acquisition_date' => $asset->acquisition_date ?: $date,
                'location_branch' => $asset->location_branch ?: $branches[$asset->id % count($branches)],
                'location_office' => $asset->location_office ?: 'Ubicación simulada '.str_pad((string) ($asset->id % 20 + 1), 2, '0', STR_PAD_LEFT),
                'assigned_to' => $asset->assigned_to ?: 'Responsable de simulación',
                'assigned_department' => $asset->assigned_department ?: $this->departmentFor($accountCode),
                'asset_status' => $asset->asset_status ?: $status,
                'useful_life_years' => $asset->useful_life_years ?: $life,
                'expected_useful_life' => ! $asset->expected_useful_life ? (
                    $life ? date('Y-m-d', strtotime($date.' +'.$life.' years')) : null
                ) : $asset->expected_useful_life,
                'note' => $this->appendSourceNote($asset->note),
                'updated_at' => now(),
            ]);

            $baseCents = $grossValues[2023][$asset->id] ?? $this->toCents((string) $asset->acquisition_value);
            $residualCents = $class['method'] === 'none'
                ? $baseCents
                : $this->multiplyDivideRounded(
                    $baseCents,
                    $this->rateToBasisPoints((string) $class['residual_rate']),
                    10000
                );
            $parameter = [
                'fixed_asset_id' => $asset->id,
                'depreciation_account_id' => $accounts[$asset->category_id]['accumulated_depreciation'],
                'method' => $class['method'],
                'depreciation_start_date' => $class['method'] === 'none' ? null : $date,
                'useful_life_years' => $life,
                'depreciable_base' => $this->fromCents($baseCents),
                'residual_value' => $this->fromCents($residualCents),
                'is_depreciable' => $class['method'] !== 'none',
                'source' => $this->source,
                'profile' => $class['profile'],
                'updated_at' => now(),
                'created_at' => now(),
            ];

            $existingParameter = DB::table('fixed_asset_depreciation_parameters')
                ->where('fixed_asset_id', $asset->id)
                ->first(['source']);
            if (! $existingParameter || $existingParameter->source === $this->source) {
                DB::table('fixed_asset_depreciation_parameters')->updateOrInsert(
                    ['fixed_asset_id' => $asset->id],
                    $parameter
                );
            }

            $profiles[$asset->id] = [
                'asset_id' => $asset->id,
                'category_id' => $asset->category_id,
                'account_id' => $accounts[$asset->category_id]['accumulated_depreciation'],
                'start_date' => $date,
                'method' => $class['method'],
                'life' => $life,
                'base_cents' => $baseCents,
                'residual_cents' => $residualCents,
            ];
        }

        return $profiles;
    }

    private function seedDepreciation(array $profiles, array $grossValues, array $accounts, string $cutoff): void
    {
        DB::table('fixed_asset_depreciation_entries')->where('source', $this->source)->delete();
        $entries = [];

        foreach ([2023, 2024] as $year) {
            $targetAssets = $this->allocateByCategory(
                $this->groupWeights($profiles, $grossValues[$year] ?? []),
                $this->toCents((string) config("sicat_simulation.note_12_references.{$year}"))
            );

            foreach ($profiles as $assetId => $profile) {
                if ($profile['method'] === 'none') {
                    continue;
                }

                $previous = $year === 2023 ? 0 : ($this->historicalAccumulated[$assetId] ?? 0);
                $current = $targetAssets[$assetId] ?? 0;
                $delta = $current - $previous;
                $this->historicalAccumulated[$assetId] = $current;
                if ($delta === 0) {
                    continue;
                }

                $entries[] = $this->depreciationEntry(
                    $profile,
                    $year,
                    "{$year}-12-31",
                    $year === 2023 ? 'opening_balance' : ($delta < 0 ? 'reversal' : 'adjustment'),
                    -$delta,
                    "{$year}-historical-reference"
                );
            }
        }

        foreach ($profiles as $assetId => $profile) {
            $accumulated = $this->historicalAccumulated[$assetId] ?? 0;
            if ($profile['method'] !== 'straight_line' || ! $profile['life']) {
                continue;
            }

            $monthlyCents = $this->multiplyDivideRounded(
                max(0, $profile['base_cents'] - $profile['residual_cents']),
                1,
                $profile['life'] * 12
            );
            $limit2025 = max(0, ($grossValues[2025][$assetId] ?? $profile['base_cents']) - $profile['residual_cents']);
            if ($accumulated > $limit2025) {
                $reversal = $accumulated - $limit2025;
                $entries[] = $this->depreciationEntry(
                    $profile,
                    2025,
                    '2025-01-01',
                    'reversal',
                    $reversal,
                    '2025-opening-revaluation-reversal'
                );
                $accumulated = $limit2025;
            }
            $year2025Charge = min(
                max(0, $limit2025 - $accumulated),
                $this->multiplyDivideRounded(
                    $monthlyCents,
                    $this->activeMonths($profile['start_date'], 2025),
                    1
                )
            );
            if ($year2025Charge > 0) {
                $entries[] = $this->depreciationEntry(
                    $profile,
                    2025,
                    '2025-12-31',
                    'depreciation',
                    -$year2025Charge,
                    '2025-line-straight'
                );
                $accumulated += $year2025Charge;
            }

            $limit2026 = max(0, ($grossValues[2026][$assetId] ?? $profile['base_cents']) - $profile['residual_cents']);
            if ($accumulated > $limit2026) {
                $reversal = $accumulated - $limit2026;
                $entries[] = $this->depreciationEntry(
                    $profile,
                    2026,
                    '2026-01-01',
                    'reversal',
                    $reversal,
                    '2026-opening-revaluation-reversal'
                );
                $accumulated = $limit2026;
            }
            $cutoffDate = Carbon::parse($cutoff);
            $start = Carbon::parse('2026-01-01');
            while ($start->lte($cutoffDate)) {
                $monthStart = $start->copy()->startOfMonth();
                $monthEnd = $start->copy()->endOfMonth();
                $chargeStart = max($monthStart->timestamp, Carbon::parse($profile['start_date'])->timestamp);
                $chargeEnd = min($monthEnd->timestamp, $cutoffDate->timestamp);
                $activeDays = $chargeEnd >= $chargeStart
                    ? (int) floor(($chargeEnd - $chargeStart) / 86400) + 1
                    : 0;
                $charge = min(
                    max(0, $limit2026 - $accumulated),
                    $this->multiplyDivideRounded($monthlyCents, $activeDays, $monthStart->daysInMonth)
                );
                if ($charge > 0) {
                    $effectiveDate = $start->copy()->endOfMonth()->gt($cutoffDate)
                        ? $cutoffDate->toDateString()
                        : $start->copy()->endOfMonth()->toDateString();
                    $entries[] = $this->depreciationEntry(
                        $profile,
                        2026,
                        $effectiveDate,
                        'depreciation',
                        -$charge,
                        '2026-'.$start->format('m')
                    );
                    $accumulated += $charge;
                }
                $start->addMonth();
            }
        }

        foreach (array_chunk($entries, 500) as $chunk) {
            DB::table('fixed_asset_depreciation_entries')->insert($chunk);
        }
    }

    private array $historicalAccumulated = [];

    private function depreciationEntry(array $profile, int $year, string $date, string $type, int $signedCents, string $key): array
    {
        return [
            'fixed_asset_id' => $profile['asset_id'],
            'fixed_asset_account_id' => $profile['account_id'],
            'period_year' => $year,
            'effective_date' => $date,
            'entry_type' => $type,
            'amount' => $this->fromCents($signedCents),
            'source_key' => "{$this->source}:{$profile['asset_id']}:{$key}",
            'source' => $this->source,
            'description' => 'Depreciación simulada por línea recta; saldo histórico ajustado por distribución por activo a nivel de cuenta.',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function allocateByCategory(array $weightsByCategory, int $targetCents): array
    {
        $categoryWeights = [];
        foreach ($weightsByCategory as $categoryId => $weights) {
            $categoryWeights[$categoryId] = array_sum($weights);
        }
        $categoryTargets = $this->allocate($categoryWeights, $targetCents);
        $assetTargets = [];

        foreach ($weightsByCategory as $categoryId => $weights) {
            $allocated = $this->allocate($weights, $categoryTargets[$categoryId] ?? 0);
            foreach ($allocated as $assetId => $amount) {
                $assetTargets[$assetId] = $amount;
            }
        }

        return $assetTargets;
    }

    private function groupWeights(array $profiles, array $assetWeights): array
    {
        $groups = [];
        foreach ($profiles as $assetId => $profile) {
            if ($profile['method'] === 'none') {
                continue;
            }
            $groups[$profile['category_id']][$assetId] = max(0, $assetWeights[$assetId] ?? 0);
        }

        return $groups;
    }

    private function allocate(array $weights, int $target): array
    {
        $weights = array_map(fn ($weight): int => max(0, (int) $weight), $weights);
        $total = array_sum($weights);
        if ($weights === [] || $target < 0) {
            throw new RuntimeException('Simulation allocation requires nonnegative targets and nonempty weights.');
        }
        if ($total === 0) {
            $weights = array_fill_keys(array_keys($weights), 1);
            $total = count($weights);
        }

        $result = [];
        $remainders = [];
        $allocated = 0;
        foreach ($weights as $key => $weight) {
            [$whole, $remainder] = $this->multiplyDivide($target, $weight, $total);
            $result[$key] = $whole;
            $allocated += $whole;
            $remainders[$key] = $remainder;
        }

        uksort($remainders, function ($left, $right) use ($remainders): int {
            $fractionComparison = $remainders[$right] <=> $remainders[$left];

            return $fractionComparison !== 0 ? $fractionComparison : ((int) $left <=> (int) $right);
        });
        foreach (array_slice(array_keys($remainders), 0, $target - $allocated) as $key) {
            $result[$key]++;
        }

        return $result;
    }

    private function rateToBasisPoints(string $rate): int
    {
        if (! preg_match('/^(0|1)(?:\.(\d{1,4}))?$/', trim($rate), $matches)) {
            throw new RuntimeException("Invalid simulation rate: {$rate}");
        }
        $fraction = (int) str_pad($matches[2] ?? '', 4, '0');
        if ($matches[1] === '1' && $fraction !== 0) {
            throw new RuntimeException("Simulation rate must not exceed 100%: {$rate}");
        }

        return ((int) ($matches[1] ?: 0) * 10000)
            + $fraction;
    }

    private function multiplyDivideRounded(int $left, int $right, int $divisor): int
    {
        [$quotient, $remainder] = $this->multiplyDivide($left, $right, $divisor);
        if ($remainder >= intdiv($divisor, 2) + ($divisor % 2)) {
            $quotient++;
        }

        return $quotient;
    }

    private function multiplyDivide(int $left, int $right, int $divisor): array
    {
        if ($left < 0 || $right < 0 || $divisor <= 0) {
            throw new RuntimeException('Simulation allocation requires nonnegative values and a positive divisor.');
        }

        $quotient = 0;
        $remainder = 0;
        $addQuotient = intdiv($right, $divisor);
        $addRemainder = $right % $divisor;

        while ($left > 0) {
            if ($left % 2 === 1) {
                $quotient += $addQuotient;
                $remainder += $addRemainder;
                if ($remainder >= $divisor) {
                    $quotient++;
                    $remainder -= $divisor;
                }
            }
            $left = intdiv($left, 2);
            if ($left > 0) {
                $addQuotient *= 2;
                $addRemainder *= 2;
                if ($addRemainder >= $divisor) {
                    $addQuotient++;
                    $addRemainder -= $divisor;
                }
            }
        }

        return [$quotient, $remainder];
    }

    private function classProfile(string $accountCode, int $assetId): array
    {
        $configured = config("sicat_simulation.asset_classes.{$accountCode}");
        $class = $configured ?? config('sicat_simulation.asset_classes.default');
        if (! is_array($class['useful_life_years'] ?? null)) {
            return $class;
        }

        $class['useful_life_years'] = $class['useful_life_years'][$assetId % count($class['useful_life_years'])];
        $class['useful_life_years'] = max(1, $class['useful_life_years'] + ($assetId % 3) - 1);

        return $class;
    }

    private function activeMonths(string $startDate, int $year): int
    {
        $start = new \DateTimeImmutable($startDate);
        $first = new \DateTimeImmutable("{$year}-01-01");
        $end = new \DateTimeImmutable("{$year}-12-31");
        if ($start > $end) {
            return 0;
        }

        $month = max($first, $start)->modify('first day of this month');
        $count = 0;
        while ($month <= $end) {
            $count++;
            $month = $month->modify('+1 month');
        }

        return min(12, $count);
    }

    private function remainingDepreciableCents(array $profile, int $accumulatedCents): int
    {
        return max(0, $profile['base_cents'] - $profile['residual_cents'] - $accumulatedCents);
    }

    private function departmentFor(string $code): string
    {
        return match (substr($code, 0, 2)) {
            '23', '24' => 'Infraestructura patrimonial simulada',
            default => 'Redes y telecomunicaciones simuladas',
        };
    }

    private function appendSourceNote(?string $note): string
    {
        $marker = 'Datos complementarios SICAT simulados.';
        $note = trim((string) $note);

        return str_contains($note, $marker) ? $note : trim($note.' '.$marker);
    }

    private function toCents(string $amount): int
    {
        if (! preg_match('/^(-?)(\d+)(?:\.(\d{1,2}))?$/', trim($amount), $matches)) {
            throw new RuntimeException("Invalid monetary amount: {$amount}");
        }

        $cents = ((int) $matches[2] * 100) + (int) str_pad($matches[3] ?? '', 2, '0');

        return ($matches[1] ?? '') === '-' ? -$cents : $cents;
    }

    private function fromCents(int $cents): string
    {
        return sprintf('%s%d.%02d', $cents < 0 ? '-' : '', intdiv(abs($cents), 100), abs($cents) % 100);
    }
}
