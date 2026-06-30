<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Alert;
use App\Models\AccountingAdjustment;
use App\Models\Dictamen;
use App\Models\Product;
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
        $productCount = Product::where('type', 'service')->count();
        $assets = Product::where('type', 'asset')->get();
        $fixedAssetCount = $assets->count();
        $productStockUnits = (int) Product::where('type', 'service')->sum('quantity');
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
            'productCount',
            'fixedAssetCount',
            'productStockUnits',
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
