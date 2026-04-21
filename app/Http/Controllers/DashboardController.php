<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
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

        return view('dashboard.index', compact(
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
            'assetPatrimonialGap'
        ));
    }
}
