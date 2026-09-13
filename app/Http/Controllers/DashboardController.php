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
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $metrics = $this->buildSicatMetrics();

        return view('dashboard.index', $metrics);
    }

    public function sicatOverview(): View
    {
        $metrics = $this->buildSicatMetrics();

        return view('sicat.overview', $metrics);
    }

    public function sicatExecutive(): View
    {
        $metrics = $this->buildSicatMetrics();

        return view('sicat.executive', $metrics);
    }

    public function exportSicatOverview(Request $request)
    {
        $metrics = $this->buildSicatMetrics();

        $pdf = Pdf::loadView('sicat.overview_pdf', $metrics);

        return $pdf->download('resumen_sicat_' . now()->format('Y-m-d') . '.pdf');
    }

    private function buildSicatMetrics(): array
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

        $assetTechnicalTotal = (float) $assets->sum('technical_value');
        $assetAccountingTotal = (float) $assets->sum('current_accounting_value');
        $assetPatrimonialGap = $assetTechnicalTotal - $assetAccountingTotal;
        $assetAcquisitionTotal = (float) $assets->sum(fn ($asset) => (float) ($asset->acquisition_value ?? (($asset->unit_cost ?? 0) * ($asset->quantity ?? 0))));
        $assetDepreciationTotal = max(0, $assetAcquisitionTotal - $assetAccountingTotal);
        $assetDepreciationRate = $assetAcquisitionTotal > 0
            ? round(($assetDepreciationTotal * 100) / $assetAcquisitionTotal, 2)
            : 0.0;
        $assetAverageDepreciationPercentage = $fixedAssetCount > 0
            ? round((float) $assets->avg(function ($asset) {
                $acquisitionValue = (float) ($asset->acquisition_value
                    ?? (($asset->unit_cost ?? 0) * ($asset->quantity ?? 0)));
                $accountingValue = (float) ($asset->current_accounting_value ?? 0);

                return $acquisitionValue > 0
                    ? min(100, max(0, (($acquisitionValue - $accountingValue) / $acquisitionValue) * 100))
                    : 0;
            }), 2)
            : 0.0;
        $assetDepreciationBuckets = [
            'Nuevo' => 0,
            'Parcialmente depreciado' => 0,
            'Altamente depreciado' => 0,
            'Totalmente depreciado' => 0,
        ];
        foreach ($assets as $asset) {
            $acquisitionValue = (float) ($asset->acquisition_value
                ?? (($asset->unit_cost ?? 0) * ($asset->quantity ?? 0)));
            $accountingValue = (float) ($asset->current_accounting_value ?? 0);
            $depreciationPercentage = $acquisitionValue > 0
                ? min(100, max(0, (($acquisitionValue - $accountingValue) / $acquisitionValue) * 100))
                : 0;

            if ($depreciationPercentage <= 0) {
                $assetDepreciationBuckets['Nuevo']++;
            } elseif ($depreciationPercentage < 50) {
                $assetDepreciationBuckets['Parcialmente depreciado']++;
            } elseif ($depreciationPercentage < 100) {
                $assetDepreciationBuckets['Altamente depreciado']++;
            } else {
                $assetDepreciationBuckets['Totalmente depreciado']++;
            }
        }
        $assetDepreciationBucketMax = max(1, max($assetDepreciationBuckets));
        $assetWithoutVerificationCount = $assets->filter(fn ($asset) => $asset->verifications->isEmpty())->count();
        $assetVerifiedCount = $fixedAssetCount - $assetWithoutVerificationCount;
        $assetVerificationRate = $fixedAssetCount > 0
            ? round(($assetVerifiedCount * 100) / $fixedAssetCount, 2)
            : 0.0;
        $assetExpiringLifeCount = $assets->filter(function ($asset) {
            if (!$asset->acquisition_date || !$asset->useful_life_years) {
                return false;
            }

            return $asset->acquisition_date->addYears($asset->useful_life_years)->between(now(), now()->addYear());
        })->count();
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
        $assetLifeAnalysis = $assets->map(function ($asset) {
            $totalYears = (int) ($asset->useful_life_years ?? 0);
            $ageYears = $asset->acquisition_date
                ? (float) $asset->acquisition_date->diffInDays(now()) / 365.25
                : null;
            $endOfLife = $asset->expected_useful_life
                ? Carbon::parse($asset->expected_useful_life)
                : ($asset->acquisition_date && $totalYears > 0
                    ? $asset->acquisition_date->copy()->addYears($totalYears)
                    : null);
            if ($totalYears === 0 && $asset->acquisition_date && $endOfLife) {
                $totalYears = max(1, (int) round($asset->acquisition_date->diffInDays($endOfLife) / 365.25));
            }
            $remainingYears = $endOfLife
                ? max(0, (float) now()->diffInDays($endOfLife, false) / 365.25)
                : ($totalYears > 0 && $ageYears !== null ? max(0, $totalYears - $ageYears) : null);
            $consumedPercentage = $totalYears > 0 && $ageYears !== null
                ? min(100, max(0, ($ageYears / $totalYears) * 100))
                : null;

            return [
                'name' => $asset->name_item,
                'total_years' => $totalYears,
                'age_years' => $ageYears,
                'remaining_years' => $remainingYears,
                'consumed_percentage' => $consumedPercentage,
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
            ->filter(fn ($life) => $life['remaining_years'] !== null && $life['remaining_years'] < 2)
            ->sortBy('remaining_years')
            ->values();
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
        $assetCategoryAnalysis = $assets
            ->groupBy('category_id')
            ->map(function ($categoryAssets, $categoryId) {
                $category = $categoryAssets->first()->category;
                $patrimonialValue = (float) $categoryAssets->sum(
                    fn ($asset) => (float) ($asset->technical_value
                        ?? $asset->current_accounting_value
                        ?? $asset->acquisition_value
                        ?? (($asset->unit_cost ?? 0) * ($asset->quantity ?? 0)))
                );

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
