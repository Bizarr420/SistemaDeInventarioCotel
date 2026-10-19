<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Alert;
use App\Models\AccountingAdjustment;
use App\Models\Dictamen;
use App\Models\FixedAsset;
use App\Models\Movement;
use App\Models\MovementItem;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockTransfer;
use App\Services\FixedAssetLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, FixedAssetLedgerService $ledger): View
    {
        $filters = $request->validate([
            'year' => 'nullable|integer|in:2023,2024,2025,2026',
            'cutoff' => 'nullable|date|after_or_equal:2026-01-01|before_or_equal:today',
        ]);
        $year = (int) ($filters['year'] ?? 2026);
        $asOf = $year === 2026
            ? ($filters['cutoff'] ?? now()->toDateString())
            : "{$year}-12-31";
        $metrics = $this->buildDashboardMetrics($ledger, $year, $asOf);

        return view('dashboard.index', $metrics);
    }

    public function sicatOverview(FixedAssetLedgerService $ledger): View
    {
        $metrics = $this->buildSicatMetrics($ledger);

        return view('sicat.overview', $metrics);
    }

    public function sicatExecutive(FixedAssetLedgerService $ledger): View
    {
        $metrics = $this->buildSicatMetrics($ledger);

        return view('sicat.executive', $metrics);
    }

    public function exportSicatOverview(Request $request, FixedAssetLedgerService $ledger)
    {
        $metrics = $this->buildSicatMetrics($ledger);

        $pdf = Pdf::loadView('sicat.overview_pdf', $metrics);

        return $pdf->download('resumen_sicat_' . now()->format('Y-m-d') . '.pdf');
    }

    private function buildDashboardMetrics(FixedAssetLedgerService $ledger, int $year, string $asOf): array
    {
        $asOfDate = Carbon::parse($asOf)->endOfDay();
        $assets = FixedAsset::query()
            ->select([
                'id',
                'category_id',
                'supplier_id',
                'name_item',
                'acquisition_date',
                'useful_life_years',
                'expected_useful_life',
                'asset_status',
                'obsolete_disposition_status',
                'assigned_to',
                'assigned_department',
                'location_branch',
                'location_office',
            ])
            ->with('latestVerification')
            ->get();
        $fixedAssetCount = $assets->count();
        $statusCounts = DB::table('fixed_assets')
            ->selectRaw('asset_status, COUNT(*) as total')
            ->groupBy('asset_status')
            ->pluck('total', 'asset_status');
        $assetOperationalCount = (int) ($statusCounts['operativo'] ?? 0);
        $assetFailureCount = (int) ($statusCounts['falla'] ?? 0);
        $assetDeterioratedCount = (int) ($statusCounts['deteriorado'] ?? 0);
        $assetObsoleteCount = (int) ($statusCounts['obsoleto'] ?? 0);
        $assetAttentionCount = $assetFailureCount + $assetDeterioratedCount + $assetObsoleteCount;
        $assetLedgerSummary = $ledger->dashboardSummary($asOf, $year);

        $verificationPendingCount = $assets->filter(
            fn (FixedAsset $asset): bool => $asset->latestVerification === null
        )->count();
        $verificationDifferenceCount = $assets->filter(function (FixedAsset $asset): bool {
            $verification = $asset->latestVerification;

            return $verification !== null
                && (strtolower((string) $verification->status) !== 'operativo'
                    || (float) ($verification->deterioration_level ?? 0) > 0);
        })->count();
        $verificationRecordedCount = $fixedAssetCount - $verificationPendingCount;
        $verificationClearCount = $verificationRecordedCount - $verificationDifferenceCount;

        $lifeBuckets = [
            '0-2 años' => 0,
            '3-5 años' => 0,
            '6-8 años' => 0,
            '9-10 años' => 0,
            'Más de 10 años' => 0,
            'Sin fecha o vida útil' => 0,
        ];
        $lifeMetrics = [];
        foreach ($assets as $asset) {
            $life = $asset->usefulLifeMetrics($asOfDate);
            $lifeMetrics[$asset->id] = $life;
            if ($life['age_years'] === null) {
                $lifeBuckets['Sin fecha o vida útil']++;
            } elseif ($life['age_years'] <= 2) {
                $lifeBuckets['0-2 años']++;
            } elseif ($life['age_years'] <= 5) {
                $lifeBuckets['3-5 años']++;
            } elseif ($life['age_years'] <= 8) {
                $lifeBuckets['6-8 años']++;
            } elseif ($life['age_years'] <= 10) {
                $lifeBuckets['9-10 años']++;
            } else {
                $lifeBuckets['Más de 10 años']++;
            }
        }
        $nearEndOfLifeCount = collect($lifeMetrics)
            ->filter(fn (array $life): bool => $life['near_end'])
            ->count();

        $missingRequiredDataCount = FixedAsset::query()
            ->where(function ($query): void {
                $query->whereNull('supplier_id')
                    ->orWhereNull('assigned_to')
                    ->orWhere('assigned_to', '')
                    ->orWhereNull('assigned_department')
                    ->orWhere('assigned_department', '')
                    ->orWhereNull('location_branch')
                    ->orWhere('location_branch', '')
                    ->orWhereNull('category_id')
                    ->orWhereNull('asset_status')
                    ->orWhere('asset_status', '')
                    ->orWhereNull('acquisition_date');
            })
            ->count();
        $profileDataGaps = DB::table('fixed_assets')
            ->selectRaw('SUM(CASE WHEN supplier_id IS NULL THEN 1 ELSE 0 END) as missing_supplier')
            ->selectRaw("SUM(CASE WHEN COALESCE(TRIM(assigned_to), '') = '' AND COALESCE(TRIM(assigned_department), '') = '' THEN 1 ELSE 0 END) as missing_assignee")
            ->selectRaw("SUM(CASE WHEN COALESCE(TRIM(location_branch), '') = '' THEN 1 ELSE 0 END) as missing_branch")
            ->selectRaw('SUM(CASE WHEN acquisition_date IS NULL THEN 1 ELSE 0 END) as missing_acquisition_date')
            ->first();
        $assetsWithoutDepreciationParameters = FixedAsset::doesntHave('depreciationParameter')->count();
        $pendingDictamens = DB::table('dictamens')->where('status', 'draft')->count();
        $pendingAdjustments = DB::table('accounting_adjustments')->where('status', 'pending')->count();

        $assetAcquisitionTotal = $assetLedgerSummary['gross'];
        $assetDepreciationTotal = $assetLedgerSummary['depreciation'];
        $assetAccountingTotal = $assetLedgerSummary['net'];
        $currencyFormatter = fn (string $amount): string => 'Bs '.$this->formatMoney($amount);
        $assetAcquisitionDisplay = $currencyFormatter((string) $assetAcquisitionTotal);
        $assetDepreciationDisplay = $currencyFormatter((string) $assetDepreciationTotal);
        $assetAccountingDisplay = $currencyFormatter((string) $assetAccountingTotal);

        $stateChart = collect([
            ['name' => 'Operativos', 'status' => 'operativo', 'count' => $assetOperationalCount, 'tone' => 'green'],
            ['name' => 'Con fallas', 'status' => 'falla', 'count' => $assetFailureCount, 'tone' => 'red'],
            ['name' => 'Deteriorados', 'status' => 'deteriorado', 'count' => $assetDeterioratedCount, 'tone' => 'amber'],
            ['name' => 'Obsoletos', 'status' => 'obsoleto', 'count' => $assetObsoleteCount, 'tone' => 'slate'],
        ])->map(fn (array $row): array => [
            'label' => $row['name'],
            'value' => $row['count'],
            'display' => number_format($row['count']),
            'url' => route('fixed-assets.index', ['asset_status' => $row['status']]),
            'tone' => $row['tone'],
        ])->all();

        $categoryCounts = DB::table('fixed_assets')
            ->selectRaw('category_id, COUNT(*) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');
        $categoryChart = collect($assetLedgerSummary['category_values'])
            ->map(fn (array $row): array => [
                'label' => $row['name'],
                'value' => (int) $row['cents'],
                'display' => $currencyFormatter($row['value']),
                'count' => (int) ($categoryCounts[$row['category_id']] ?? 0),
                'url' => route('fixed-assets.index', ['category_id' => $row['category_id']]),
                'tone' => 'blue',
            ])
            ->sortByDesc('value')
            ->take(6)
            ->values()
            ->all();

        $departmentChart = $assets
            ->groupBy(fn (FixedAsset $asset): string => $asset->assigned_department ?: ($asset->assigned_to ?: 'Sin departamento'))
            ->map(fn ($group, string $name): array => [
                'label' => $name,
                'value' => $group->count(),
                'display' => number_format($group->count()),
                'url' => route('fixed-assets.index', ['assignee' => $name === 'Sin departamento' ? '__unassigned' : $name]),
                'tone' => 'indigo',
            ])
            ->sortByDesc('value')
            ->take(6)
            ->values()
            ->all();
        $branchChart = $assets
            ->groupBy(fn (FixedAsset $asset): string => $asset->location_branch ?: 'Sin sucursal')
            ->map(fn ($group, string $name): array => [
                'label' => $name,
                'value' => $group->count(),
                'display' => number_format($group->count()),
                'url' => route('fixed-assets.index', ['branch' => $name === 'Sin sucursal' ? '__unassigned' : $name]),
                'tone' => 'violet',
            ])
            ->sortByDesc('value')
            ->take(6)
            ->values()
            ->all();
        $ageChart = collect($lifeBuckets)->map(function (int $count, string $range): array {
            $filters = match ($range) {
                '0-2 años' => ['min_age' => 0, 'max_age' => 2],
                '3-5 años' => ['min_age' => 3, 'max_age' => 5],
                '6-8 años' => ['min_age' => 6, 'max_age' => 8],
                '9-10 años' => ['min_age' => 9, 'max_age' => 10],
                'Más de 10 años' => ['min_age' => 11],
                default => ['alert' => 'incomplete_data'],
            };

            return [
                'label' => $range,
                'value' => $count,
                'display' => number_format($count),
                'url' => route('fixed-assets.index', $filters),
                'tone' => 'cyan',
            ];
        })->values()->all();

        $analysisCharts = [
            'category' => ['label' => 'Categoría · valor bruto', 'rows' => $categoryChart, 'currency' => true],
            'department' => ['label' => 'Departamento · cantidad', 'rows' => $departmentChart, 'currency' => false],
            'branch' => ['label' => 'Sucursal · cantidad', 'rows' => $branchChart, 'currency' => false],
            'age' => ['label' => 'Antigüedad · cantidad', 'rows' => $ageChart, 'currency' => false],
        ];

        $activity = $this->recentFixedAssetActivity();
        $latestVerification = DB::table('asset_verifications')
            ->orderByDesc('verified_at')
            ->value('verified_at');

        $productCount = Product::where('type', 'service')->count();
        $inventorySummary = DB::table('product_stocks')
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->where('products.type', 'service')
            ->selectRaw('COALESCE(SUM(product_stocks.current_stock), 0) as units')
            ->selectRaw('COALESCE(SUM(products.unit_cost * product_stocks.current_stock), 0) as value')
            ->first();
        $inventoryValueDisplay = $this->formatMoney((string) ($inventorySummary->value ?? '0'));

        $userCanManageAssets = in_array(auth()->user()->role ?? '', ['admin', 'contable', 'tecnico'], true);
        $currentCutoff = $year === 2026 ? $asOf : now()->toDateString();
        $dashboardFilter = ['year' => $year];

        return compact(
            'year',
            'asOf',
            'currentCutoff',
            'dashboardFilter',
            'fixedAssetCount',
            'assetOperationalCount',
            'assetAttentionCount',
            'assetFailureCount',
            'assetDeterioratedCount',
            'assetObsoleteCount',
            'assetAcquisitionTotal',
            'assetAcquisitionDisplay',
            'assetDepreciationTotal',
            'assetDepreciationDisplay',
            'assetAccountingTotal',
            'assetAccountingDisplay',
            'assetLedgerSummary',
            'verificationPendingCount',
            'verificationDifferenceCount',
            'verificationRecordedCount',
            'verificationClearCount',
            'nearEndOfLifeCount',
            'missingRequiredDataCount',
            'profileDataGaps',
            'assetsWithoutDepreciationParameters',
            'pendingDictamens',
            'pendingAdjustments',
            'stateChart',
            'analysisCharts',
            'activity',
            'latestVerification',
            'productCount',
            'inventorySummary',
            'inventoryValueDisplay',
            'userCanManageAssets'
        );
    }

    private function formatMoney(string $amount): string
    {
        if (! preg_match('/^(-?)(\d+)(?:\.(\d{1,2}))?$/', trim($amount), $matches)) {
            throw new \InvalidArgumentException("Invalid dashboard monetary amount: {$amount}");
        }

        $whole = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $matches[2]);
        $fraction = str_pad($matches[3] ?? '', 2, '0');

        return ($matches[1] ?? '').$whole.','.$fraction;
    }

    private function recentFixedAssetActivity(): array
    {
        $movements = DB::table('fixed_asset_movements as movements')
            ->join('fixed_assets as assets', 'assets.id', '=', 'movements.fixed_asset_id')
            ->orderByDesc('movements.effective_date')
            ->limit(5)
            ->get([
                'assets.name_item',
                'movements.movement_type as type',
                'movements.effective_date as happened_at',
                'movements.description',
                'movements.source',
            ])
            ->map(fn ($row): array => [
                'name' => $row->name_item,
                'type' => match ($row->type) {
                    'acquisition' => 'Alta',
                    'disposal' => 'Baja',
                    'status_change' => 'Cambio de estado',
                    default => 'Movimiento',
                },
                'date' => $row->happened_at,
                'description' => $row->description,
                'simulated' => str_starts_with((string) $row->source, 'USER_SIMULATION'),
            ]);
        $depreciation = DB::table('fixed_asset_depreciation_entries as entries')
            ->join('fixed_assets as assets', 'assets.id', '=', 'entries.fixed_asset_id')
            ->orderByDesc('entries.effective_date')
            ->limit(5)
            ->get([
                'assets.name_item',
                'entries.entry_type as type',
                'entries.effective_date as happened_at',
                'entries.description',
                'entries.source',
            ])
            ->map(fn ($row): array => [
                'name' => $row->name_item,
                'type' => 'Depreciación',
                'date' => $row->happened_at,
                'description' => $row->description,
                'simulated' => str_starts_with((string) $row->source, 'SICAT_SIMULATION')
                    || str_starts_with((string) $row->source, 'USER_SIMULATION'),
            ]);
        $verifications = DB::table('asset_verifications as verifications')
            ->join('fixed_assets as assets', 'assets.id', '=', 'verifications.fixed_asset_id')
            ->orderByDesc('verifications.verified_at')
            ->limit(5)
            ->get([
                'assets.name_item',
                'verifications.status',
                'verifications.verified_at as happened_at',
            ])
            ->map(fn ($row): array => [
                'name' => $row->name_item,
                'type' => 'Verificación',
                'date' => $row->happened_at,
                'description' => 'Resultado: '.$row->status,
                'simulated' => false,
            ]);

        return $movements->concat($depreciation)
            ->concat($verifications)
            ->sortByDesc('date')
            ->take(8)
            ->values()
            ->all();
    }

    private function buildSicatMetrics(FixedAssetLedgerService $ledger): array
    {
        $products = Product::where('type', 'service')->with('stocks')->get();
        $productCount = $products->count();
        $totalItems = Product::count();
        $stockRows = ProductStock::whereHas('product', fn ($query) => $query->where('type', 'service'))->get();
        $availableStockUnits = (int) $stockRows->sum('current_stock');
        $inUseStockUnits = (int) $stockRows->sum('in_use_stock');
        $reservedStockUnits = (int) $stockRows->sum('reserved_stock');
        $repairStockUnits = (int) $stockRows->sum('repair_stock');
        $damagedStockUnits = (int) $stockRows->sum('damaged_stock');
        $lostStockUnits = (int) $stockRows->sum('lost_stock');
        $disposedStockUnits = (int) $stockRows->sum('disposed_stock');
        $otherStockUnits = (int) $stockRows->sum('other_stock');
        $pendingLiquidationUnits = (int) MovementItem::where('liquidation_status', 'pending')
            ->whereHas('movement.product', fn ($query) => $query->where('type', 'service'))
            ->count();
        $stockTotalUnits = $availableStockUnits
            + $inUseStockUnits
            + $reservedStockUnits
            + $repairStockUnits
            + $damagedStockUnits
            + $lostStockUnits
            + $disposedStockUnits
            + $otherStockUnits
            + $pendingLiquidationUnits;
        $productStockUnits = $availableStockUnits;
        $productInventoryValue = (float) $products->sum(
            fn ($product) => (float) ($product->unit_cost ?? 0) * (int) $product->stocks->sum('current_stock')
        );
        $lowStockThreshold = 10;
        $productsWithStockCount = $products->filter(fn ($product) => $product->total_stock > 0)->count();
        $outOfStockProductCount = $products->filter(fn ($product) => $product->total_stock <= 0)->count();
        $lowStockProductCount = $products->filter(fn ($product) => (int) $product->quantity <= $lowStockThreshold)->count();
        $productsWithoutMovementCount = $products->filter(fn ($product) => $product->movements()->doesntExist())->count();
        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();
        $periodMovements = Movement::whereBetween('created_at', [$periodStart, $periodEnd]);
        $periodEntries = (int) (clone $periodMovements)
            ->where('type', 'in')
            ->where('movement_kind', 'original')
            ->where(function ($query) {
                $query->whereNull('reason')->orWhere('reason', 'not like', 'Devolución%');
            })
            ->sum('quantity');
        $periodExits = (int) (clone $periodMovements)
            ->where('type', 'out')
            ->where('movement_kind', 'original')
            ->sum('quantity');
        $periodReturns = (int) (clone $periodMovements)
            ->where('type', 'in')
            ->where('reason', 'like', 'Devolución%')
            ->sum('quantity');
        $transfersInTransit = StockTransfer::where('status', 'in_transit')->count();
        $warehouseTransfers = (int) Movement::where('type', 'transfer')->count();

        $assets = FixedAsset::with(['verifications', 'category', 'supplier'])->get();
        $fixedAssetCount = $assets->count();
        $fixedAssetUnits = (int) $assets->sum('quantity');
        $obsoleteProductCount = Product::where('type', 'service')->get()->filter(fn($p) => $p->isObsolete())->count();
        $obsoleteFixedAssetCount = $assets->where('asset_status', 'obsoleto')->count();

        $assetOperationalCount = $assets->where('asset_status', 'operativo')->count();
        $assetFailureCount = $assets->where('asset_status', 'falla')->count();
        $assetDeterioratedCount = $assets->where('asset_status', 'deteriorado')->count();
        $assetObsoleteCount = $assets->where('asset_status', 'obsoleto')->count();

        $assetFailureRate = $fixedAssetCount > 0
            ? round(($assetFailureCount * 100) / $fixedAssetCount, 2)
            : 0.0;

        $assetHighDeteriorationCount = $assets
            ->filter(fn($asset) => $asset->calculateDeterioration() <= 30)
            ->count();

        $assetHighDeteriorationRate = $fixedAssetCount > 0
            ? round(($assetHighDeteriorationCount * 100) / $fixedAssetCount, 2)
            : 0.0;

        $assetObsoletePendingCount = $assets
            ->where('asset_status', 'obsoleto')
            ->where('obsolete_disposition_status', 'pendiente')
            ->count();
        $assetObsoleteSoldCount = $assets
            ->where('asset_status', 'obsoleto')
            ->where('obsolete_disposition_status', 'vendido')
            ->count();
        $assetObsoleteDestroyedCount = $assets
            ->where('asset_status', 'obsoleto')
            ->where('obsolete_disposition_status', 'destruido')
            ->count();

        $assetObsoleteRate = $fixedAssetCount > 0
            ? round(($assetObsoleteCount * 100) / $fixedAssetCount, 2)
            : 0.0;

        $assetTotalEstimatedValue = (float) $assets->sum(
            fn ($asset) => (float) ($asset->technical_value
                ?? $asset->acquisition_value
                ?? (($asset->unit_cost ?? 0) * ($asset->quantity ?? 0)))
        );

        $assetAvgUnitCost = $fixedAssetCount > 0
            ? round((float) $assets->avg('unit_cost'), 2)
            : 0.0;

        $assetAvgDeterioration = $fixedAssetCount > 0
            ? round((float) $assets->avg(fn($asset) => $asset->calculateDeterioration()), 2)
            : 0.0;

        $assetLedgerSummary = $ledger->dashboardSummary(now()->toDateString());
        $assetTechnicalTotal = (float) $assets->sum('technical_value');
        $assetAccountingTotal = (float) $assetLedgerSummary['net'];
        $assetPatrimonialGap = $assetTechnicalTotal - $assetAccountingTotal;
        $assetAcquisitionTotal = (float) $assetLedgerSummary['gross'];
        $assetDepreciationTotal = (float) $assetLedgerSummary['depreciation'];
        $assetDepreciationRate = $assetAcquisitionTotal > 0
            ? round(($assetDepreciationTotal * 100) / $assetAcquisitionTotal, 2)
            : 0.0;
        $assetAverageDepreciationPercentage = $assetDepreciationRate;
        $assetDepreciationBuckets = $assetLedgerSummary['depreciation_buckets'];
        $assetDepreciationBucketMax = $assetLedgerSummary['depreciation_bucket_max'];
        $assetWithoutVerificationCount = $assets->filter(fn ($asset) => $asset->verifications->isEmpty())->count();
        $assetVerifiedCount = $fixedAssetCount - $assetWithoutVerificationCount;
        $assetVerificationRate = $fixedAssetCount > 0
            ? round(($assetVerifiedCount * 100) / $fixedAssetCount, 2)
            : 0.0;
        $assetExpiringLifeCount = 0;
        $assetBranchAnalysis = $assets
            ->groupBy(fn ($asset) => $asset->location_branch ?: 'Sin sucursal')
            ->map(function ($branchAssets, $branch) {
                return [
                    'name' => $branch,
                    'count' => $branchAssets->count(),
                    'value' => (float) $branchAssets->sum(
                        fn ($asset) => (float) ($asset->technical_value
                            ?? $asset->current_accounting_value
                            ?? $asset->acquisition_value
                            ?? (($asset->unit_cost ?? 0) * ($asset->quantity ?? 0)))
                    ),
                ];
            })
            ->sortByDesc('count')
            ->values();
        $assetBranchMaxCount = max(1, (int) $assetBranchAnalysis->max('count'));
        $assetBranchMaxValue = max(1, (float) $assetBranchAnalysis->max('value'));
        $assetDepartmentAnalysis = $assets
            ->groupBy(fn ($asset) => $asset->assigned_department ?: ($asset->assigned_to ?: 'Sin departamento'))
            ->map(function ($departmentAssets, $department) {
                return [
                    'name' => $department,
                    'count' => $departmentAssets->count(),
                    'value' => (float) $departmentAssets->sum(
                        fn ($asset) => (float) ($asset->technical_value
                            ?? $asset->current_accounting_value
                            ?? $asset->acquisition_value
                            ?? (($asset->unit_cost ?? 0) * ($asset->quantity ?? 0)))
                    ),
                    'failures' => $departmentAssets->where('asset_status', 'falla')->count(),
                    'obsolete' => $departmentAssets->where('asset_status', 'obsoleto')->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();
        $assetDepartmentMaxCount = max(1, (int) $assetDepartmentAnalysis->max('count'));
        $assetDepartmentMaxValue = max(1, (float) $assetDepartmentAnalysis->max('value'));
        $assetDepartmentMaxFailures = max(1, (int) $assetDepartmentAnalysis->max('failures'));
        $assetDepartmentMaxObsolete = max(1, (int) $assetDepartmentAnalysis->max('obsolete'));
        $assetSupplierAnalysis = $assets
            ->groupBy(fn ($asset) => $asset->supplier?->name ?? 'Sin proveedor')
            ->map(function ($supplierAssets, $supplier) {
                $supplierModel = $supplierAssets->first()->supplier;
                return [
                    'id' => $supplierModel?->id,
                    'name' => $supplier,
                    'count' => $supplierAssets->count(),
                    'value' => (float) $supplierAssets->sum(
                        fn ($asset) => (float) ($asset->acquisition_value
                            ?? (($asset->unit_cost ?? 0) * ($asset->quantity ?? 0)))
                    ),
                    'failures' => $supplierAssets->where('asset_status', 'falla')->count(),
                    'obsolete' => $supplierAssets->where('asset_status', 'obsoleto')->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();
        $assetSupplierMaxCount = max(1, (int) $assetSupplierAnalysis->max('count'));
        $assetSupplierMaxValue = max(1, (float) $assetSupplierAnalysis->max('value'));
        $assetSupplierMaxFailures = max(1, (int) $assetSupplierAnalysis->max('failures'));
        $assetSupplierMaxObsolete = max(1, (int) $assetSupplierAnalysis->max('obsolete'));
        $ageBuckets = [
            '0-2 años' => 0,
            '3-5 años' => 0,
            '6-8 años' => 0,
            '9-10 años' => 0,
            'Más de 10 años' => 0,
            'Sin fecha de adquisición' => 0,
        ];
        $assetLifeAnalysis = $assets->map(function (FixedAsset $asset) {
            return [
                'name' => $asset->name_item,
                ...$asset->usefulLifeMetrics(),
            ];
        });
        foreach ($assetLifeAnalysis as $life) {
            if ($life['age_years'] === null) {
                $ageBuckets['Sin fecha de adquisición']++;
            } elseif ($life['age_years'] <= 2) {
                $ageBuckets['0-2 años']++;
            } elseif ($life['age_years'] <= 5) {
                $ageBuckets['3-5 años']++;
            } elseif ($life['age_years'] <= 8) {
                $ageBuckets['6-8 años']++;
            } elseif ($life['age_years'] <= 10) {
                $ageBuckets['9-10 años']++;
            } else {
                $ageBuckets['Más de 10 años']++;
            }
        }
        $assetNearEndOfLife = $assetLifeAnalysis
            ->filter(fn ($life) => $life['near_end'])
            ->sortBy('remaining_years')
            ->values();
        $assetExpiringLifeCount = $assetNearEndOfLife->count();
        $assetAgeMaxCount = max(1, max($ageBuckets));
        $latestAssetVerifications = $assets->mapWithKeys(function ($asset) {
            return [$asset->id => $asset->verifications->sortByDesc('verified_at')->first()];
        });
        $assetVerificationPendingCount = $assets->filter(
            fn ($asset) => !$latestAssetVerifications->get($asset->id)
        )->count();
        $assetVerificationTotalCount = $fixedAssetCount - $assetVerificationPendingCount;
        $assetVerificationDifferenceCount = $assets->filter(function ($asset) use ($latestAssetVerifications) {
            $verification = $latestAssetVerifications->get($asset->id);

            return $verification
                && (strtolower((string) $verification->status) !== 'operativo'
                    || (float) ($verification->deterioration_level ?? 0) > 0);
        })->count();
        $assetVerificationVerifiedCount = max(
            0,
            $fixedAssetCount - $assetVerificationPendingCount - $assetVerificationDifferenceCount
        );
        $lastAssetVerification = $assets
            ->flatMap(fn ($asset) => $asset->verifications)
            ->sortByDesc('verified_at')
            ->first();
        $assetAlertCounts = [
            'failures' => $assetFailureCount,
            'high_deterioration' => $assetHighDeteriorationCount,
            'near_end_of_life' => $assetNearEndOfLife->count(),
            'pending_verification' => $assetVerificationPendingCount,
            'obsolete' => $assetObsoleteCount,
            'verification_differences' => $assetVerificationDifferenceCount,
        ];
        $assetLatestVerifications = $assets->flatMap(fn ($asset) => $asset->verifications->map(fn ($verification) => [
            'asset' => $asset->name_item,
            'date' => $verification->verified_at,
            'status' => $verification->status,
        ]))->sortByDesc('date')->take(5);
        $ledgerCategoryValues = collect($assetLedgerSummary['category_values'])->keyBy('category_id');
        $assetCategoryAnalysis = $assets
            ->groupBy('category_id')
            ->map(function ($categoryAssets, $categoryId) use ($ledgerCategoryValues) {
                $category = $categoryAssets->first()->category;
                $patrimonialValue = (float) ($ledgerCategoryValues->get($categoryId)['value'] ?? 0);

                return [
                    'id' => $categoryId,
                    'name' => $category?->name ?? 'Sin categoría',
                    'count' => $categoryAssets->count(),
                    'value' => $patrimonialValue,
                ];
            })
            ->sortByDesc('count')
            ->values();
        $assetCategoryMaxCount = max(1, (int) $assetCategoryAnalysis->max('count'));
        $assetCategoryMaxValue = max(1, (float) $assetCategoryAnalysis->max('value'));

        $unreadAlerts = Alert::where('is_read', false)->count();
        $totalProducts = Product::count();
        $obsoleteProducts = Product::all()->filter(fn($p) => $p->isObsolete())->count();
        $totalAlerts = Alert::count();

        $totalDictamens = Dictamen::count();
        $pendingDictamens = Dictamen::where('status', 'draft')->count();
        $approvedDictamens = Dictamen::where('status', 'approved')->count();

        $totalAdjustments = AccountingAdjustment::count();
        $postedAdjustments = AccountingAdjustment::where('status', 'posted')->count();
        $pendingAdjustments = AccountingAdjustment::where('status', 'pending')->count();
        $deteriorationAdjustments = AccountingAdjustment::where('adjustment_type', 'deterioration')->count();
        $disposalAdjustments = AccountingAdjustment::where('adjustment_type', 'disposal')->count();

        return compact(
            'unreadAlerts',
            'totalProducts',
            'obsoleteProducts',
            'totalAlerts',
            'totalItems',
            'productCount',
            'fixedAssetCount',
            'productStockUnits',
            'stockTotalUnits',
            'availableStockUnits',
            'pendingLiquidationUnits',
            'inUseStockUnits',
            'reservedStockUnits',
            'repairStockUnits',
            'damagedStockUnits',
            'lostStockUnits',
            'disposedStockUnits',
            'productInventoryValue',
            'productsWithStockCount',
            'lowStockProductCount',
            'outOfStockProductCount',
            'productsWithoutMovementCount',
            'periodEntries',
            'periodExits',
            'periodReturns',
            'transfersInTransit',
            'warehouseTransfers',
            'fixedAssetUnits',
            'obsoleteProductCount',
            'obsoleteFixedAssetCount',
            'assetOperationalCount',
            'assetFailureCount',
            'assetDeterioratedCount',
            'assetObsoleteCount',
            'assetFailureRate',
            'assetHighDeteriorationCount',
            'assetHighDeteriorationRate',
            'assetObsoletePendingCount',
            'assetObsoleteSoldCount',
            'assetObsoleteDestroyedCount',
            'assetObsoleteRate',
            'assetTotalEstimatedValue',
            'assetAvgUnitCost',
            'assetAvgDeterioration',
            'assetTechnicalTotal',
            'assetAccountingTotal',
            'assetLedgerSummary',
            'assetPatrimonialGap',
            'assetAcquisitionTotal',
            'assetDepreciationTotal',
            'assetDepreciationRate',
            'assetAverageDepreciationPercentage',
            'assetDepreciationBuckets',
            'assetDepreciationBucketMax',
            'assetWithoutVerificationCount',
            'assetVerifiedCount',
            'assetVerificationRate',
            'assetExpiringLifeCount',
            'assetBranchAnalysis',
            'assetBranchMaxCount',
            'assetBranchMaxValue',
            'assetDepartmentAnalysis',
            'assetDepartmentMaxCount',
            'assetDepartmentMaxValue',
            'assetDepartmentMaxFailures',
            'assetDepartmentMaxObsolete',
            'assetSupplierAnalysis',
            'assetSupplierMaxCount',
            'assetSupplierMaxValue',
            'assetSupplierMaxFailures',
            'assetSupplierMaxObsolete',
            'ageBuckets',
            'assetAgeMaxCount',
            'assetLifeAnalysis',
            'assetNearEndOfLife',
            'assetVerificationPendingCount',
            'assetVerificationTotalCount',
            'assetVerificationDifferenceCount',
            'assetVerificationVerifiedCount',
            'lastAssetVerification',
            'assetAlertCounts',
            'assetLatestVerifications',
            'assetCategoryAnalysis',
            'assetCategoryMaxCount',
            'assetCategoryMaxValue',
            'totalDictamens',
            'pendingDictamens',
            'approvedDictamens',
            'totalAdjustments',
            'postedAdjustments',
            'pendingAdjustments',
            'deteriorationAdjustments',
            'disposalAdjustments'
        );
    }
}
