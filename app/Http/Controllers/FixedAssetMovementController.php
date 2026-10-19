<?php

namespace App\Http\Controllers;

use App\Models\FixedAsset;
use App\Services\FixedAssetLedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class FixedAssetMovementController extends Controller
{
    public function dispose(Request $request, FixedAsset $fixed_asset, FixedAssetLedgerService $ledger): RedirectResponse
    {
        $data = $request->validate([
            'obsolete_disposition_status' => 'required|in:vendido,destruido',
            'effective_date' => ['required', 'date', 'after_or_equal:2026-01-01', 'before_or_equal:today'],
            'amount' => 'required|numeric|decimal:0,2|min:0.01',
        ]);
        $request->merge([
            'movement_type' => 'disposal',
            'disposition' => $data['obsolete_disposition_status'],
            'status' => 'obsoleto',
            'description' => 'Baja simulada autorizada: '.$data['obsolete_disposition_status'].'.',
        ]);

        return $this->store($request, $fixed_asset, $ledger);
    }

    public function createAcquisition(): View
    {
        $categories = DB::table('fixed_asset_accounts')
            ->join('categories', 'categories.id', '=', 'fixed_asset_accounts.category_id')
            ->where('fixed_asset_accounts.account_type', 'fixed_asset')
            ->orderBy('fixed_asset_accounts.account_code')
            ->get(['categories.id', 'fixed_asset_accounts.account_code', 'fixed_asset_accounts.description']);

        return view('fixed-assets.acquisition', compact('categories'));
    }

    public function acquire(Request $request, FixedAssetLedgerService $ledger): RedirectResponse
    {
        $data = $request->validate([
            'internal_code' => 'nullable|string|max:100|unique:fixed_assets,internal_code',
            'name_item' => 'required|string|max:150',
            'description' => 'nullable|string',
            'category_id' => 'required|integer|exists:categories,id',
            'effective_date' => ['required', 'date', 'after_or_equal:2026-01-01', 'before_or_equal:today'],
            'amount' => 'required|numeric|decimal:0,2|min:0.01',
            'location_branch' => 'nullable|string|max:120',
            'location_office' => 'nullable|string|max:120',
            'assigned_to' => 'nullable|string|max:150',
            'assigned_department' => 'nullable|string|max:150',
            'useful_life_years' => 'nullable|integer|min:1|max:100',
            'residual_value' => 'nullable|numeric|decimal:0,2|min:0',
        ]);

        $this->assertOpen2026($ledger);
        $amountCents = $this->toCents((string) $data['amount']);
        $residualCents = $this->toCents((string) ($data['residual_value'] ?? '0'));
        if ($residualCents > $amountCents) {
            throw ValidationException::withMessages(['residual_value' => 'El valor residual no puede superar el costo del activo.']);
        }

        $accountId = DB::table('fixed_asset_accounts')
            ->where('category_id', $data['category_id'])
            ->where('account_type', 'fixed_asset')
            ->value('id');
        if (! $accountId) {
            throw ValidationException::withMessages([
                'category_id' => 'La categoría no está vinculada a una cuenta contable de activos fijos.',
            ]);
        }

        $asset = DB::transaction(function () use ($data, $accountId, $amountCents, $residualCents): FixedAsset {
            $amount = $this->fromCents($amountCents);
            $asset = FixedAsset::create([
                'internal_code' => $data['internal_code'] ?? 'SICAT-'.Str::uuid(),
                'name_item' => $data['name_item'],
                'description' => $data['description'] ?? 'Alta 2026 simulada/registrada en SICAT.',
                'category_id' => $data['category_id'],
                'quantity' => 1,
                'unit_cost' => $amount,
                'acquisition_value' => $amount,
                'current_accounting_value' => $amount,
                'acquisition_date' => $data['effective_date'],
                'location_branch' => $data['location_branch'] ?? null,
                'location_office' => $data['location_office'] ?? null,
                'assigned_to' => $data['assigned_to'] ?? null,
                'assigned_department' => $data['assigned_department'] ?? null,
                'asset_status' => 'operativo',
                'note' => 'Alta de activo 2026; origen USER_SIMULATION.',
            ]);

            DB::table('fixed_asset_movements')->insert([
                'fixed_asset_id' => $asset->id,
                'fixed_asset_account_id' => $accountId,
                'period_year' => 2026,
                'effective_date' => $data['effective_date'],
                'movement_type' => 'acquisition',
                'amount' => $amount,
                'status' => 'operativo',
                'location' => $data['location_branch'] ?? null,
                'source_key' => 'USER_SIMULATION:'.Str::uuid(),
                'source' => 'USER_SIMULATION',
                'description' => 'Alta de activo con fecha efectiva informada por el usuario.',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (! empty($data['useful_life_years'])) {
                $depreciationAccountId = DB::table('fixed_asset_accounts')
                    ->where('category_id', $data['category_id'])
                    ->where('account_type', 'accumulated_depreciation')
                    ->value('id');
                if (! $depreciationAccountId) {
                    throw ValidationException::withMessages([
                        'category_id' => 'Falta la cuenta de depreciación acumulada para esta categoría.',
                    ]);
                }

                $parameterValues = [
                    'fixed_asset_id' => $asset->id,
                    'depreciation_account_id' => $depreciationAccountId,
                    'method' => 'straight_line',
                    'depreciation_start_date' => $data['effective_date'],
                    'useful_life_years' => $data['useful_life_years'],
                    'depreciable_base' => $amount,
                    'residual_value' => $this->fromCents($residualCents),
                    'is_depreciable' => true,
                    'source' => 'USER_SIMULATION',
                    'profile' => 'Alta nueva configurada por usuario; escenario no oficial.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $parameterId = DB::table('fixed_asset_depreciation_parameters')->insertGetId($parameterValues);
                DB::table('fixed_asset_depreciation_parameter_audits')->insert([
                    'depreciation_parameter_id' => $parameterId,
                    'change_type' => 'created',
                    'old_values' => null,
                    'new_values' => json_encode([
                        'method' => $parameterValues['method'],
                        'depreciation_start_date' => $parameterValues['depreciation_start_date'],
                        'useful_life_years' => $parameterValues['useful_life_years'],
                        'depreciable_base' => $parameterValues['depreciable_base'],
                        'residual_value' => $parameterValues['residual_value'],
                        'depreciation_account_id' => $parameterValues['depreciation_account_id'],
                        'source' => $parameterValues['source'],
                    ], JSON_THROW_ON_ERROR),
                    'source' => 'USER_SIMULATION',
                    'changed_by' => auth()->id(),
                    'changed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->seedCurrentYearDepreciation(
                    $asset->id,
                    $depreciationAccountId,
                    $data['effective_date'],
                    $amount,
                    $this->fromCents($residualCents),
                    (int) $data['useful_life_years']
                );
            }

            return $asset;
        });

        return redirect()->route('fixed-assets.show', $asset)->with('success', 'Alta 2026 registrada con fecha efectiva.');
    }

    public function store(Request $request, FixedAsset $fixed_asset, FixedAssetLedgerService $ledger): RedirectResponse
    {
        $data = $request->validate([
            'movement_type' => 'required|in:addition,disposal,revaluation,transfer,status_change',
            'effective_date' => ['required', 'date', 'after_or_equal:2026-01-01', 'before_or_equal:today'],
            'amount' => 'nullable|numeric|decimal:0,2',
            'disposition' => 'nullable|in:vendido,destruido',
            'status' => 'nullable|in:operativo,falla,deteriorado,obsoleto',
            'location' => 'nullable|string|max:180',
            'description' => 'required|string|max:2000',
        ]);

        $this->assertOpen2026($ledger);
        $amountCents = $this->toCents((string) ($data['amount'] ?? '0'));
        if (in_array($data['movement_type'], ['addition', 'disposal'], true) && $amountCents <= 0) {
            throw ValidationException::withMessages(['amount' => 'El importe debe ser mayor que cero.']);
        }
        if ($data['movement_type'] === 'revaluation' && $amountCents === 0) {
            throw ValidationException::withMessages(['amount' => 'La revaluación debe ser distinta de cero.']);
        }
        if ($data['movement_type'] === 'disposal' && empty($data['disposition'])) {
            throw ValidationException::withMessages(['disposition' => 'Indica si el activo fue vendido o destruido.']);
        }

        $asset = DB::transaction(function () use ($data, $fixed_asset, $ledger, $amountCents): FixedAsset {
            $asset = FixedAsset::query()->lockForUpdate()->findOrFail($fixed_asset->id);
            $accountId = DB::table('fixed_asset_valuations')
                ->where('fixed_asset_id', $asset->id)
                ->where('valuation_year', 2026)
                ->value('fixed_asset_account_id');
            if (! $accountId) {
                $accountId = DB::table('fixed_asset_accounts')
                    ->where('category_id', $asset->category_id)
                    ->where('account_type', 'fixed_asset')
                    ->value('id');
            }
            if (! $accountId) {
                throw ValidationException::withMessages([
                    'movement_type' => 'El activo no tiene una cuenta contable vinculada.',
                ]);
            }

            if ($data['movement_type'] === 'disposal') {
                $balance = $ledger->assetBalanceForCutoff($asset->id, $data['effective_date']);
                $grossCents = $this->toCents($balance['gross']);
                if ($amountCents > $grossCents) {
                    throw ValidationException::withMessages(['amount' => 'La baja no puede superar el saldo bruto del activo al corte.']);
                }
                $depreciationAccountId = DB::table('fixed_asset_depreciation_parameters')
                    ->where('fixed_asset_id', $asset->id)
                    ->value('depreciation_account_id');
                $accumulatedCents = $this->toCents($balance['depreciation']);
                $releasedDepreciation = $grossCents > 0
                    ? $this->multiplyDivideRounded($accumulatedCents, $amountCents, $grossCents)
                    : 0;
                if ($releasedDepreciation > 0 && $depreciationAccountId) {
                    DB::table('fixed_asset_depreciation_entries')->insert([
                        'fixed_asset_id' => $asset->id,
                        'fixed_asset_account_id' => $depreciationAccountId,
                        'period_year' => 2026,
                        'effective_date' => $data['effective_date'],
                        'entry_type' => 'reversal',
                        'amount' => $this->fromCents($releasedDepreciation),
                        'source_key' => 'USER_SIMULATION:DISPOSAL:'.Str::uuid(),
                        'source' => 'USER_SIMULATION',
                        'description' => 'Reversión de depreciación proporcional a la baja simulada.',
                        'created_by' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $amountCents *= -1;
            }
            $status = $data['status'] ?? null;
            if ($data['movement_type'] === 'disposal') {
                $status = 'obsoleto';
            }
            $location = $data['location'] ?? null;

            DB::table('fixed_asset_movements')->insert([
                'fixed_asset_id' => $asset->id,
                'fixed_asset_account_id' => $accountId,
                'period_year' => 2026,
                'effective_date' => $data['effective_date'],
                'movement_type' => $data['movement_type'],
                'amount' => $this->fromCents($amountCents),
                'status' => $status,
                'location' => $location,
                'source_key' => 'USER_SIMULATION:'.Str::uuid(),
                'source' => 'USER_SIMULATION',
                'description' => $data['description'],
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($status !== null || $location !== null) {
                $asset->update(array_filter([
                    'asset_status' => $status,
                    'obsolete_disposition_status' => $data['disposition'] ?? null,
                    'location_branch' => $location,
                ], fn ($value): bool => $value !== null));
            }

            return $asset;
        });

        return redirect()->route('fixed-assets.show', $asset)->with('success', 'Movimiento 2026 registrado con fecha efectiva.');
    }

    private function assertOpen2026(FixedAssetLedgerService $ledger): void
    {
        try {
            $ledger->assertOpenPeriod(2026);
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages(['effective_date' => $exception->getMessage()]);
        }
    }

    private function seedCurrentYearDepreciation(
        int $assetId,
        int $accountId,
        string $startDate,
        string $baseAmount,
        string $residualAmount,
        int $usefulLifeYears
    ): void {
        $start = Carbon::parse($startDate);
        $cutoff = now();
        $baseCents = $this->toCents($baseAmount);
        $residualCents = $this->toCents($residualAmount);
        $monthlyCents = $this->multiplyDivideRounded(max(0, $baseCents - $residualCents), 1, $usefulLifeYears * 12);
        $remainingCents = max(0, $baseCents - $residualCents);
        $month = $start->copy()->startOfMonth();

        while ($month->lte($cutoff)) {
            $monthStart = $month->copy();
            $monthEnd = $month->copy()->endOfMonth();
            $activeStart = max($start->timestamp, $monthStart->timestamp);
            $activeEnd = min($monthEnd->timestamp, $cutoff->timestamp);
            $activeDays = max(0, (int) floor(($activeEnd - $activeStart) / 86400) + 1);
            $charge = min(
                $remainingCents,
                $this->multiplyDivideRounded($monthlyCents, $activeDays, $monthStart->daysInMonth)
            );
            if ($charge > 0) {
                $effectiveDate = $monthEnd->gt($cutoff) ? $cutoff->toDateString() : $monthEnd->toDateString();
                DB::table('fixed_asset_depreciation_entries')->insert([
                    'fixed_asset_id' => $assetId,
                    'fixed_asset_account_id' => $accountId,
                    'period_year' => 2026,
                    'effective_date' => $effectiveDate,
                    'entry_type' => 'depreciation',
                    'amount' => $this->fromCents(-$charge),
                    'source_key' => "USER_SIMULATION:ACQ:{$assetId}:".$month->format('Y-m'),
                    'source' => 'USER_SIMULATION',
                    'description' => 'Depreciación mensual por línea recta desde fecha de alta informada.',
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $remainingCents -= $charge;
            }
            $month->addMonth();
        }
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
            throw new RuntimeException('Monetary proportional calculations require nonnegative values and a positive divisor.');
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
}
