<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $products = Product::with(['stocks.warehouse'])
            ->orderBy('name_item')
            ->paginate(20)
            ->withQueryString();

        return view('dashboard.index', compact('products'));
    }
}
