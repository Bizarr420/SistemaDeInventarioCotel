<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class FinancialNotesReportService
{
    private const YEARS = ['2023', '2024', '2025', '2026'];

    public function __construct(private readonly FixedAssetLedgerService $ledger)
    {
    }

    /**
     * @return array{note11: array<string, mixed>, note12: array<string, mixed>}
     */
    public function buildAssetNotes(string $cutoffDate, string $management = 'all'): array
    {
        $values11 = [];
        $values12 = [];
        foreach (self::YEARS as $year) {
            $values11[$year] = $this->ledger->note11Balances((int) $year, $cutoffDate);
            $values12[$year] = $this->ledger->note12Balances((int) $year, $cutoffDate);
        }

        $rows11 = $this->rowsForAccounts($values11, $management);
        $rows12 = $this->rowsForAccounts($values12, $management);
        $totals11 = $this->totals($values11, $rows11);
        $totals12 = $this->totals($values12, $rows12);
        $references11 = config('sicat_simulation.note_11_references', []);
        $references12 = config('sicat_simulation.note_12_references', []);
        $recordedThrough = collect([
            DB::table('fixed_asset_valuations')
                ->where('valuation_year', 2026)
                ->whereDate('as_of_date', '<=', $cutoffDate)
                ->max('as_of_date'),
            DB::table('fixed_asset_movements')
                ->where('period_year', 2026)
                ->whereDate('effective_date', '<=', $cutoffDate)
                ->max('effective_date'),
            DB::table('fixed_asset_depreciation_entries')
                ->where('period_year', 2026)
                ->whereDate('effective_date', '<=', $cutoffDate)
                ->max('effective_date'),
        ])->filter()->max();
        $accountsSources = DB::table('fixed_asset_valuations')
            ->whereBetween('valuation_year', [2023, 2026])
            ->select('source')
            ->distinct()
            ->pluck('source')
            ->filter()
            ->values()
            ->all();

        $note11 = [
            'code' => 'Nota 11',
            'title' => 'Activos fijos',
            'description' => 'Composición del rubro de activos fijos por cuenta contable, con saldos al cierre y al corte seleccionado.',
            'years' => self::YEARS,
            'rows' => $rows11,
            'totals' => $totals11,
            'estimated_years' => [],
            'source_warning' => 'Datos de escenario SICAT simulado. La asignación por cuenta de 2023 y 2024 reproduce los totales de referencia distribuyendo importes por activo en proporción a la planilla archivada; no es conciliación contable oficial.',
            'movement_warning' => 'Los movimientos efectivos de 2026 se agregan al saldo de apertura del escenario y se excluyen si son posteriores al corte. Los periodos cerrados mantienen snapshots históricos.',
            'sources' => $accountsSources,
            'cutoff_date' => $cutoffDate,
            'dated_2026_count' => DB::table('fixed_asset_valuations')
                ->where('valuation_year', 2026)
                ->whereNotNull('as_of_date')
                ->whereDate('as_of_date', '<=', $cutoffDate)
                ->count(),
            'future_2026_count' => DB::table('fixed_asset_valuations')
                ->where('valuation_year', 2026)
                ->whereDate('as_of_date', '>', $cutoffDate)
                ->count(),
            'undated_2026_count' => DB::table('fixed_asset_valuations')
                ->where('valuation_year', 2026)
                ->whereNull('as_of_date')
                ->count(),
            'undated_2026_total' => $this->undated2026Total(),
            'recorded_through_date' => $recordedThrough,
            'future_movement_count' => DB::table('fixed_asset_movements')
                ->where('period_year', 2026)
                ->whereDate('effective_date', '>', $cutoffDate)
                ->count(),
            'future_depreciation_count' => DB::table('fixed_asset_depreciation_entries')
                ->where('period_year', 2026)
                ->whereDate('effective_date', '>', $cutoffDate)
                ->count(),
            'historical_validation' => $this->validate($references11, $totals11),
            'account_discrepancies' => $this->archivedDifferences($values11),
            'simulated' => true,
        ];

        $note12 = [
            'code' => 'Nota 12',
            'title' => 'Depreciación acumulada de activos fijos',
            'description' => 'Saldo acumulado de depreciación por cuenta; la contrapartida se guarda con signo negativo y esta nota muestra la magnitud positiva de la referencia publicada.',
            'years' => self::YEARS,
            'rows' => $rows12,
            'totals' => $totals12,
            'estimated_years' => [],
            'source_warning' => 'Datos de depreciación del escenario SICAT simulado; requieren validación de método, vidas útiles, valores residuales y asignación por cuenta antes de cualquier uso oficial.',
            'sign_warning' => 'Los asientos simulados de depreciación se almacenan con signo negativo como contra-activo. La nota presenta magnitudes positivas para seguir la convención de los totales históricos de referencia.',
            'historical_validation' => $this->validate($references12, $totals12),
            'account_discrepancies' => [],
            'simulated' => true,
            'cutoff_date' => $cutoffDate,
        ];

        return ['note11' => $note11, 'note12' => $note12];
    }

    private function rowsForAccounts(array $yearValues, string $management): array
    {
        $codes = [];
        foreach ($yearValues as $year => $accounts) {
            foreach ($accounts as $code => $balance) {
                $codes[$code] = true;
            }
        }

        $rows = [];
        foreach (array_keys($codes) as $code) {
            $values = array_fill_keys(self::YEARS, null);
            $detail = '';
            foreach (self::YEARS as $year) {
                if (! isset($yearValues[$year][$code])) {
                    continue;
                }
                $values[$year] = $yearValues[$year][$code]['value'];
                $detail = $yearValues[$year][$code]['detail'];
            }

            if ($management !== 'all' && $values[$management] === null) {
                continue;
            }

            $firstRecorded = null;
            foreach (self::YEARS as $year) {
                if ($values[$year] !== null) {
                    $firstRecorded = $year;
                    break;
                }
            }

            $rows[] = [
                'code' => $code,
                'detail' => $detail,
                'values' => $values,
                'status' => $firstRecorded === '2023' ? 'Con registro histórico' : 'Registro desde '.$firstRecorded,
                'cutoff_status' => $values['2026'] !== null ? 'Saldo base más movimientos al corte' : 'Sin saldo al corte',
                'variations' => [
                    '2024_2023' => $this->variation($values['2024'], $values['2023']),
                    '2025_2024' => $this->variation($values['2025'], $values['2024']),
                    '2026_cutoff' => null,
                ],
            ];
        }

        usort($rows, static fn (array $left, array $right): int => strcmp($left['code'], $right['code']));

        return $rows;
    }

    private function totals(array $yearValues, array $rows): array
    {
        $totals = [];
        foreach (self::YEARS as $year) {
            $sum = 0;
            $hasValue = false;
            foreach ($rows as $row) {
                if ($row['values'][$year] === null) {
                    continue;
                }
                $sum += $this->toCents($row['values'][$year]);
                $hasValue = true;
            }

            $totals[$year] = $hasValue ? $this->fromCents($sum) : null;
        }

        return $totals;
    }

    private function validate(array $references, array $actual): array
    {
        $validation = [];
        foreach ($references as $year => $reference) {
            $actualAmount = $actual[(string) $year] ?? null;
            $validation[(string) $year] = [
                'reference' => (string) $reference,
                'actual' => $actualAmount,
                'account_reference_sum' => $actualAmount,
                'difference' => $actualAmount === null
                    ? null
                    : $this->fromCents($this->toCents($actualAmount) - $this->toCents((string) $reference)),
                'status' => $actualAmount === null ? 'Sin dato' : ($actualAmount === $reference ? 'Conciliado en simulación' : 'Diferencia'),
            ];
        }

        return $validation;
    }

    private function undated2026Total(): ?string
    {
        $amounts = DB::table('fixed_asset_valuations')
            ->where('valuation_year', 2026)
            ->whereNull('as_of_date')
            ->pluck('amount');
        if ($amounts->isEmpty()) {
            return null;
        }

        $cents = 0;
        foreach ($amounts as $amount) {
            $cents += $this->toCents((string) $amount);
        }

        return $this->fromCents($cents);
    }

    private function archivedDifferences(array $yearValues): array
    {
        $archived = DB::table('fixed_asset_valuation_originals as originals')
            ->join('fixed_assets', 'fixed_assets.id', '=', 'originals.fixed_asset_id')
            ->join('categories', 'categories.id', '=', 'fixed_assets.category_id')
            ->leftJoin('fixed_asset_accounts as accounts', 'accounts.category_id', '=', 'categories.id')
            ->whereIn('originals.valuation_year', [2023, 2024])
            ->where(function ($query): void {
                $query->whereNull('accounts.account_type')
                    ->orWhere('accounts.account_type', 'fixed_asset');
            })
            ->get([
                'originals.valuation_year',
                'originals.amount',
                'accounts.account_code',
                'categories.name as category_name',
            ]);
        $oldTotals = [];
        foreach ($archived as $record) {
            $code = $record->account_code;
            if (! $code && preg_match('/^Cuenta (\d{8})$/', $record->category_name, $matches)) {
                $code = $matches[1];
            }
            if (! $code) {
                continue;
            }
            $year = (string) $record->valuation_year;
            $oldTotals[$year][$code] = ($oldTotals[$year][$code] ?? 0) + $this->toCents((string) $record->amount);
        }

        $differences = [];
        foreach ([2023, 2024] as $year) {
            foreach ($oldTotals[(string) $year] ?? [] as $code => $cents) {
                $current = $yearValues[(string) $year][$code]['cents'] ?? null;
                $delta = $current === null ? null : $current - $cents;
                if ($delta === null || $delta === 0) {
                    continue;
                }
                $differences[] = [
                    'year' => (string) $year,
                    'code' => $code,
                    'detail' => $yearValues[(string) $year][$code]['detail'] ?? '',
                    'actual' => $this->fromCents($current),
                    'reference' => $this->fromCents($cents),
                    'difference' => $this->fromCents($delta),
                    'status' => 'Distribución simulada frente al archivo original',
                ];
            }
        }

        return $differences;
    }

    private function variation(?string $current, ?string $previous): ?string
    {
        if ($current === null || $previous === null) {
            return null;
        }

        return $this->fromCents($this->toCents($current) - $this->toCents($previous));
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
