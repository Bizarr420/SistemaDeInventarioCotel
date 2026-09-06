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

        $assets = FixedAsset::all();
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

        $assetTotalEstimatedValue = $assets->sum(function ($asset) {
            return (float) ($asset->unit_cost ?? 0) * (int) ($asset->quantity ?? 0);
        });

        $assetAvgUnitCost = $fixedAssetCount > 0
            ? round((float) $assets->avg('unit_cost'), 2)
            : 0.0;

        $assetAvgDeterioration = $fixedAssetCount > 0
            ? round((float) $assets->avg(fn($asset) => $asset->calculateDeterioration()), 2)
            : 0.0;

        $assetTechnicalTotal = (float) $assets->sum('technical_value');
        $assetAccountingTotal = (float) $assets->sum('current_accounting_value');
        $assetPatrimonialGap = $assetTechnicalTotal - $assetAccountingTotal;

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
