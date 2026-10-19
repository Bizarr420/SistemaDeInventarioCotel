<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class FixedAssetLedgerService
{
    public function note11Balances(int $year, string $cutoffDate): array
    {
        $balances = [];
        $query = DB::table('fixed_asset_valuations as valuations')
            ->join('fixed_assets as assets', 'assets.id', '=', 'valuations.fixed_asset_id')
            ->join('categories', 'categories.id', '=', 'assets.category_id')
            ->leftJoin('fixed_asset_accounts as accounts', 'accounts.id', '=', 'valuations.fixed_asset_account_id')
            ->where('valuations.valuation_year', $year)
            ->when($year === 2026, fn ($builder) => $builder->whereDate('valuations.as_of_date', '<=', $cutoffDate))
            ->get([
                'assets.category_id',
                'accounts.account_code',
                'accounts.description as account_description',
                'categories.name as category_name',
                'categories.description as category_description',
                'valuations.amount',
            ]);

        foreach ($query as $record) {
            $code = $record->account_code ?: $this->accountCodeFromCategory($record->category_name);
            if ($code === null) {
                continue;
            }

            $balances[$code]['detail'] = $record->account_description ?: ($record->category_description ?: $record->category_name);
            $balances[$code]['category_id'] = (int) $record->category_id;
            $balances[$code]['cents'] = ($balances[$code]['cents'] ?? 0) + $this->toCents((string) $record->amount);
        }

        if ($year === 2026) {
            $movements = DB::table('fixed_asset_movements as movements')
                ->join('fixed_asset_accounts as accounts', 'accounts.id', '=', 'movements.fixed_asset_account_id')
                ->where('movements.period_year', 2026)
                ->whereDate('movements.effective_date', '<=', $cutoffDate)
                ->get([
                    'accounts.account_code',
                    'accounts.category_id',
                    'accounts.description as account_description',
                    'movements.amount',
                ]);

            foreach ($movements as $movement) {
                $code = $movement->account_code;
                $balances[$code]['detail'] = $movement->account_description;
                $balances[$code]['category_id'] = (int) $movement->category_id;
                $balances[$code]['cents'] = ($balances[$code]['cents'] ?? 0) + $this->toCents((string) $movement->amount);
            }
        }

        return $this->formatBalances($balances);
    }

    public function note12Balances(int $year, string $cutoffDate): array
    {
        $balances = [];
        $entries = DB::table('fixed_asset_depreciation_entries as entries')
            ->join('fixed_asset_accounts as accounts', 'accounts.id', '=', 'entries.fixed_asset_account_id')
            ->where('entries.period_year', '<=', $year)
            ->when($year === 2026, fn ($builder) => $builder->whereDate('entries.effective_date', '<=', $cutoffDate))
            ->whereDate('entries.effective_date', '<=', $year === 2026 ? $cutoffDate : "{$year}-12-31")
            ->get([
                'accounts.account_code',
                'accounts.description',
                'entries.amount',
                'entries.source',
            ]);

        foreach ($entries as $entry) {
            $code = $entry->account_code;
            $balances[$code]['detail'] = $entry->description ?: 'Depreciación acumulada';
            $balances[$code]['cents'] = ($balances[$code]['cents'] ?? 0) - $this->toCents((string) $entry->amount);
            $balances[$code]['simulated'] = ($balances[$code]['simulated'] ?? false)
                || str_starts_with((string) $entry->source, 'SICAT_SIMULATION');
        }

        return $this->formatBalances($balances);
    }

    public function dashboardSummary(string $cutoffDate, int $year = 2026): array
    {
        $gross = $this->note11Balances($year, $cutoffDate);
        $depreciation = $this->note12Balances($year, $cutoffDate);
        $grossCents = array_sum(array_column($gross, 'cents'));
        $depreciationCents = array_sum(array_column($depreciation, 'cents'));
        $assetRows = DB::table('fixed_assets')
            ->leftJoin('fixed_asset_depreciation_entries as entries', function ($join) use ($cutoffDate, $year): void {
                $join->on('entries.fixed_asset_id', '=', 'fixed_assets.id')
                    ->whereDate('entries.effective_date', '<=', $cutoffDate)
                    ->where('entries.period_year', '<=', $year);
            })
            ->leftJoin('fixed_asset_depreciation_parameters as parameters', 'parameters.fixed_asset_id', '=', 'fixed_assets.id')
            ->groupBy('fixed_assets.id', 'fixed_assets.acquisition_value', 'parameters.depreciable_base')
            ->get([
                'fixed_assets.id',
                'fixed_assets.acquisition_value',
                'parameters.depreciable_base',
                DB::raw('COUNT(DISTINCT parameters.id) as has_depreciation_parameters'),
                DB::raw('SUM(entries.amount) as depreciation_signed'),
            ]);
        $buckets = [
            'Sin parámetros' => 0,
            'Nuevo' => 0,
            'Parcialmente depreciado' => 0,
            'Altamente depreciado' => 0,
            'Totalmente depreciado' => 0,
        ];
        foreach ($assetRows as $asset) {
            if ((int) $asset->has_depreciation_parameters === 0) {
                $buckets['Sin parámetros']++;

                continue;
            }

            $base = $this->toCents((string) ($asset->depreciable_base ?? $asset->acquisition_value ?? '0'));
            $depreciated = abs($this->toCents((string) ($asset->depreciation_signed ?? '0')));
            $percentage = $base > 0 ? min(100, ($depreciated * 100) / $base) : 0;
            $buckets[$percentage <= 0 ? 'Nuevo' : ($percentage < 50 ? 'Parcialmente depreciado' : ($percentage < 100 ? 'Altamente depreciado' : 'Totalmente depreciado'))]++;
        }

        $categoryValues = [];
        foreach ($gross as $accountCode => $balance) {
            $categoryValues[] = [
                'category_id' => $balance['category_id'] ?? null,
                'account_code' => $accountCode,
                'name' => $balance['detail'],
                'cents' => $balance['cents'],
                'value' => $this->fromCents($balance['cents']),
            ];
        }

        $acquisitions = DB::table('fixed_asset_movements')
            ->where('period_year', $year)
            ->where('movement_type', 'acquisition')
            ->whereDate('effective_date', '<=', $cutoffDate)
            ->count();
        $disposals = DB::table('fixed_asset_movements')
            ->where('period_year', $year)
            ->where('movement_type', 'disposal')
            ->whereDate('effective_date', '<=', $cutoffDate)
            ->count();

        return [
            'gross_cents' => $grossCents,
            'gross' => $this->fromCents($grossCents),
            'depreciation_cents' => $depreciationCents,
            'depreciation' => $this->fromCents($depreciationCents),
            'net_cents' => $grossCents - $depreciationCents,
            'net' => $this->fromCents($grossCents - $depreciationCents),
            'depreciation_buckets' => $buckets,
            'depreciation_bucket_max' => max(1, max($buckets)),
            'category_values' => $categoryValues,
            'acquisitions' => $acquisitions,
            'disposals' => $disposals,
            'unconfigured_assets' => $buckets['Sin parámetros'],
            'simulated' => true,
            'cutoff_date' => $cutoffDate,
        ];
    }

    public function assetBalanceForCutoff(int $assetId, string $cutoffDate): array
    {
        $valuations = DB::table('fixed_asset_valuations')
            ->where('fixed_asset_id', $assetId)
            ->where('valuation_year', 2026)
            ->whereDate('as_of_date', '<=', $cutoffDate)
            ->get(['amount']);
        $grossCents = 0;
        foreach ($valuations as $valuation) {
            $grossCents += $this->toCents((string) $valuation->amount);
        }
        $grossCents += $this->sumMovementCents($assetId, $cutoffDate);
        $depreciationCents = -$this->sumDepreciationCents($assetId, $cutoffDate);

        return [
            'gross' => $this->fromCents($grossCents),
            'depreciation' => $this->fromCents($depreciationCents),
            'net' => $this->fromCents($grossCents - $depreciationCents),
            'simulated' => true,
        ];
    }

    public function assertOpenPeriod(int $year): void
    {
        $period = DB::table('fixed_asset_periods')->where('year', $year)->first();
        if (! $period || $period->status !== 'open') {
            throw new RuntimeException("Fixed-asset period {$year} is not open for operations.");
        }
    }

    private function sumMovementCents(int $assetId, string $cutoffDate): int
    {
        $sum = 0;
        $movements = DB::table('fixed_asset_movements')
            ->where('fixed_asset_id', $assetId)
            ->whereDate('effective_date', '<=', $cutoffDate)
            ->get(['amount']);
        foreach ($movements as $movement) {
            $sum += $this->toCents((string) $movement->amount);
        }

        return $sum;
    }

    private function sumDepreciationCents(int $assetId, string $cutoffDate): int
    {
        $sum = 0;
        $entries = DB::table('fixed_asset_depreciation_entries')
            ->where('fixed_asset_id', $assetId)
            ->whereDate('effective_date', '<=', $cutoffDate)
            ->get(['amount']);
        foreach ($entries as $entry) {
            $sum += $this->toCents((string) $entry->amount);
        }

        return $sum;
    }

    private function formatBalances(array $balances): array
    {
        foreach ($balances as &$balance) {
            $balance['value'] = $this->fromCents($balance['cents']);
        }
        unset($balance);
        ksort($balances, SORT_STRING);

        return $balances;
    }

    private function accountCodeFromCategory(string $categoryName): ?string
    {
        return preg_match('/^Cuenta (\d{8})$/', $categoryName, $matches) ? $matches[1] : null;
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
