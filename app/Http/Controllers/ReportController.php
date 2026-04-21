<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\Product;
use App\Models\ProductStock;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function summary(): View
    {
        return view('reports.summary', [
            'totalProducts' => Product::where('type', 'service')->count(),
            'totalStock' => ProductStock::whereHas('product', function ($query) {
                $query->where('type', 'service');
            })->sum('current_stock'),
            'lastMovements' => Movement::with(['product', 'warehouse'])
                ->whereHas('product', function ($query) {
                    $query->where('type', 'service');
                })
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function kardex(Request $request): View
    {
        $productId = $request->integer('product_id');
        $product = $productId ? Product::where('type', 'service')
            ->with(['movements' => function ($query) {
                $query->orderBy('created_at');
            }, 'movements.warehouse', 'movements.user'])
            ->find($productId) : null;

        return view('reports.kardex', [
            'product' => $product,
            'products' => Product::where('type', 'service')->orderBy('name_item')->get(),
        ]);
    }

    public function deterioration(): View
    {
        $products = Product::where('type', 'asset')->get()->map(function ($product) {
            return [
                'product' => $product,
                'useful_life_percentage' => $product->calculateUsefulLife(),
                'deterioration' => $product->calculateDeterioration(),
                'patrimonial_deviation' => $product->calculatePatrimonialDeviation(),
            ];
        });

        return view('reports.deterioration', compact('products'));
    }

    public function comparative(): View
    {
        $products = Product::where('type', 'asset')
            ->whereNotNull('technical_value')
            ->whereNotNull('current_accounting_value')
            ->get();

        return view('reports.comparative', compact('products'));
    }

    public function exportObsolescence(Request $request)
    {
        $products = Product::where('type', 'asset')->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name_item,
                'internal_code' => $product->internal_code,
                'is_obsolete' => $product->isObsolete() ? 'Sí' : 'No',
                'useful_life_percentage' => $product->calculateUsefulLife(),
                'deterioration' => $product->calculateDeterioration(),
                'patrimonial_deviation' => $product->calculatePatrimonialDeviation(),
                'end_of_support' => $product->end_of_support?->format('d/m/Y'),
                'compatibility_status' => $product->compatibility_status,
                'operational_capacity' => $product->operational_capacity,
            ];
        });

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('reports.obsolescence_pdf', compact('products'));
            return $pdf->download('reporte_obsolescencia_' . now()->format('Y-m-d') . '.pdf');
        }

        if ($request->format === 'excel') {
            return Excel::download(new \App\Exports\ObsolescenceExport($products), 'reporte_obsolescencia_' . now()->format('Y-m-d') . '.xlsx');
        }

        return back()->with('error', 'Formato no soportado');
    }
}
