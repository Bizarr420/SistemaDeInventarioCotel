<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index', [
    'products'   => \App\Models\Product::count(),
    'categories' => \App\Models\Category::count(),
    'suppliers'  => \App\Models\Supplier::count(),
    'stock'      => \App\Models\Product::sum('stock'),
        ]);
    }
}
