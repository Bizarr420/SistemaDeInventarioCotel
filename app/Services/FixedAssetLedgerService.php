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
            ->when($year === 2026, fn ($builder) => $builder->where('valuations.as_of_date', '<=', $cutoffDate.' 23:59:59.999999'))
            ->selectRaw('
                assets.category_id,
                accounts.account_code,
                accounts.description as account_description,
                categories.name as category_name,
                categories.description as category_description,
                SUM(CAST(ROUND(valuations.amount * 100, 0) AS INTEGER)) as amount_cents
            ')
            ->groupBy(
                'assets.category_id',
                'accounts.account_code',
                'accounts.description',
                'categories.name',
                'categories.description',
            )
            ->get();

        foreach ($query as $record) {
            $code = $record->account_code ?: $this->accountCodeFromCategory($record->category_name);
            if ($code === null) {
                continue;
            }

            $balances[$code]['detail'] = $record->account_description ?: ($record->category_description ?: $record->category_name);
            $balances[$code]['category_id'] = (int) $record->category_id;
            $balances[$code]['cents'] = ($balances[$code]['cents'] ?? 0) + (int) $record->amount_cents;
        }

        if ($year === 2026) {
            $movements = DB::table('fixed_asset_movements as movements')
                ->join('fixed_asset_accounts as accounts', 'accounts.id', '=', 'movements.fixed_asset_account_id')
                ->where('movements.period_year', 2026)
                ->where('movements.effective_date', '<=', $cutoffDate.' 23:59:59.999999')
                ->selectRaw('
                    accounts.account_code,
                    accounts.category_id,
                    accounts.description as account_description,
                    SUM(CAST(ROUND(movements.amount * 100, 0) AS INTEGER)) as amount_cents
                ')
                ->groupBy('accounts.account_code', 'accounts.category_id', 'accounts.description')
                ->get();

            foreach ($movements as $movement) {
                $code = $movement->account_code;
                $balances[$code]['detail'] = $movement->account_description;
                $balances[$code]['category_id'] = (int) $movement->category_id;
                $balances[$code]['cents'] = ($balances[$code]['cents'] ?? 0) + (int) $movement->amount_cents;
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
            ->when($year === 2026, fn ($builder) => $builder->where('entries.effective_date', '<=', $cutoffDate.' 23:59:59.999999'))
            ->where('entries.effective_date', '<=', ($year === 2026 ? $cutoffDate : "{$year}-12-31").' 23:59:59.999999')
            ->selectRaw('
                accounts.account_code,
                accounts.description,
                SUM(CAST(ROUND(entries.amount * 100, 0) AS INTEGER)) as amount_cents,
                MAX(CASE WHEN entries.source LIKE \'SICAT_SIMULATION%\' THEN 1 ELSE 0 END) as is_simulated
            ')
            ->groupBy('accounts.account_code', 'accounts.description')
            ->get();

        foreach ($entries as $entry) {
            $code = $entry->account_code;
            $balances[$code]['detail'] = $entry->description ?: 'Depreciación acumulada';
            $balances[$code]['cents'] = ($balances[$code]['cents'] ?? 0) - (int) $entry->amount_cents;
            $balances[$code]['simulated'] = ($balances[$code]['simulated'] ?? false)
                || (bool) $entry->is_simulated;
        }

        return $this->formatBalances($balances);
    }

    public function dashboardSummary(string $cutoffDate, int $year = 2026): array
    {
        $gross = $this->note11Balances($year, $cutoffDate);
        $depreciation = $this->note12Balances($year, $cutoffDate);
        $grossCents = array_sum(array_column($gross, 'cents'));
        $depreciationCents = array_sum(array_column($depreciation, 'cents'));
        $entriesByAsset = DB::table('fixed_asset_depreciation_entries')
            ->selectRaw('
                fixed_asset_id,
                SUM(CAST(ROUND(amount * 100, 0) AS INTEGER)) as depreciation_cents
            ')
            ->where('period_year', '<=', $year)
            ->where('effective_date', '<=', $cutoffDate.' 23:59:59.999999')
            ->groupBy('fixed_asset_id');
        $bucketRows = DB::table('fixed_assets as assets')
            ->leftJoin('fixed_asset_depreciation_parameters as parameters', 'parameters.fixed_asset_id', '=', 'assets.id')
            ->leftJoinSub($entriesByAsset, 'entries', 'entries.fixed_asset_id', '=', 'assets.id')
            ->selectRaw('
                CASE
                    WHEN parameters.id IS NULL THEN \'Sin parámetros\'
                    WHEN ABS(COALESCE(entries.depreciation_cents, 0)) <= 0 THEN \'Nuevo\'
                    WHEN COALESCE(parameters.depreciable_base, assets.acquisition_value, 0) <= 0 THEN \'Nuevo\'
                    WHEN ABS(COALESCE(entries.depreciation_cents, 0)) >= ROUND(COALESCE(parameters.depreciable_base, assets.acquisition_value, 0) * 100, 0) THEN \'Totalmente depreciado\'
                    WHEN ABS(COALESCE(entries.depreciation_cents, 0)) * 2 < ROUND(COALESCE(parameters.depreciable_base, assets.acquisition_value, 0) * 100, 0) THEN \'Parcialmente depreciado\'
                    ELSE \'Altamente depreciado\'
                END as bucket,
                COUNT(*) as total
            ')
            ->groupBy('bucket')
            ->pluck('total', 'bucket');
        $buckets = [
            'Sin parámetros' => (int) ($bucketRows['Sin parámetros'] ?? 0),
            'Nuevo' => (int) ($bucketRows['Nuevo'] ?? 0),
            'Parcialmente depreciado' => (int) ($bucketRows['Parcialmente depreciado'] ?? 0),
            'Altamente depreciado' => (int) ($bucketRows['Altamente depreciado'] ?? 0),
            'Totalmente depreciado' => (int) ($bucketRows['Totalmente depreciado'] ?? 0),
        ];

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

        $movementCounts = DB::table('fixed_asset_movements')
            ->where('period_year', $year)
            ->where('effective_date', '<=', $cutoffDate.' 23:59:59.999999')
            ->selectRaw('movement_type, COUNT(*) as total')
            ->groupBy('movement_type')
            ->pluck('total', 'movement_type');
        $acquisitions = (int) ($movementCounts['acquisition'] ?? 0);
        $disposals = (int) ($movementCounts['disposal'] ?? 0);

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
        $grossCents = (int) DB::table('fixed_asset_valuations')
            ->where('fixed_asset_id', $assetId)
            ->where('valuation_year', 2026)
            ->where('as_of_date', '<=', $cutoffDate.' 23:59:59.999999')
            ->sum(DB::raw('CAST(ROUND(amount * 100, 0) AS INTEGER)'));
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
        return (int) DB::table('fixed_asset_movements')
            ->where('fixed_asset_id', $assetId)
            ->where('effective_date', '<=', $cutoffDate.' 23:59:59.999999')
            ->sum(DB::raw('CAST(ROUND(amount * 100, 0) AS INTEGER)'));
    }

    private function sumDepreciationCents(int $assetId, string $cutoffDate): int
    {
        return (int) DB::table('fixed_asset_depreciation_entries')
            ->where('fixed_asset_id', $assetId)
            ->where('effective_date', '<=', $cutoffDate.' 23:59:59.999999')
            ->sum(DB::raw('CAST(ROUND(amount * 100, 0) AS INTEGER)'));
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
