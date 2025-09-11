<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Schema;
use App\Models\Product;        // ✅ ESTE es el correcto
// opcional si usas sumas de stock directamente:
use App\Models\ProductStock;   // (por si lo necesitas en el dashboard)

class DashboardController extends Controller
{
    public function index()
    {
        $products = Product::with(['stocks.warehouse'])
            ->orderBy('name_item')   // o el campo que prefieras
            ->paginate(20)           // ✅ ahora $products es un LengthAwarePaginator
            ->withQueryString();

        return view('dashboard.index', compact('products'));
    }
}
