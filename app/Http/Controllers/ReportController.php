<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function summary() {
    return view('reports.summary', [
        'totalProducts' => \App\Models\Product::count(),
        'totalStock'    => \App\Models\Product::sum('stock'),
        'lastMoves'     => \App\Models\Movement::with('product')->latest()->limit(10)->get(),
    ]);
    }

    public function kardex() {
    $product = \App\Models\Product::with('movements')->findOrFail(request('product_id'));
    $moves = $product->movements()->orderBy('created_at')->get();
    // puedes calcular el saldo acumulado en la vista o aquí
    return view('reports.kardex', compact('product','moves'));
    }
}
