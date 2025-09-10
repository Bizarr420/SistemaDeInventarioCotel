<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // DashboardController@index
        return view('dashboard.index', [
        'products'   => \App\Models\Product::count(),
        'stock'      => \App\Models\ProductStock::sum('current_stock'),
        ]);

    }
}
