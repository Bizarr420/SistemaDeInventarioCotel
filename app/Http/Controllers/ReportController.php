<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function summary(): View
    {
        return view('reports.summary', [
            'totalProducts' => Product::count(),
            'totalStock' => ProductStock::sum('current_stock'),
            'lastMovements' => Movement::with(['product', 'warehouse'])->latest()->limit(10)->get(),
        ]);
    }

    public function kardex(Request $request): View
    {
        $productId = $request->integer('product_id');
        $product = $productId ? Product::with(['movements' => function ($query) {
            $query->orderBy('created_at');
        }, 'movements.warehouse', 'movements.user'])->find($productId) : null;

        return view('reports.kardex', [
            'product' => $product,
            'products' => Product::orderBy('name_item')->get(),
        ]);
    }
}
