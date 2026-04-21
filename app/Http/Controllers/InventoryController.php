<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use App\Models\Supplier;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display a listing of the inventory (services, routers, cables, etc.).
     */
    public function index()
    {
        $products = Product::with(['category', 'warehouse', 'supplier'])
            ->where('type', 'service') // or filter by category if needed
            ->paginate(15);

        return view('inventory.index', compact('products'));
    }

    /**
     * Show the form for creating a new inventory item.
     */
    public function create()
    {
        $categories = Category::all();
        $warehouses = Warehouse::all();
        $suppliers = Supplier::all();

        return view('inventory.create', compact('categories', 'warehouses', 'suppliers'));
    }

    /**
     * Store a newly created inventory item in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'quantity' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'sku' => 'nullable|string|unique:products,sku',
        ]);

        $validated['type'] = 'service';

        Product::create($validated);

        return redirect()->route('inventory.index')->with('success', 'Producto de inventario creado exitosamente.');
    }

    /**
     * Show the form for editing the specified inventory item.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        $warehouses = Warehouse::all();
        $suppliers = Supplier::all();

        return view('inventory.edit', compact('product', 'categories', 'warehouses', 'suppliers'));
    }

    /**
     * Update the specified inventory item in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'quantity' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
        ]);

        $product->update($validated);

        return redirect()->route('inventory.index')->with('success', 'Producto de inventario actualizado exitosamente.');
    }

    /**
     * Remove the specified inventory item from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('inventory.index')->with('success', 'Producto de inventario eliminado exitosamente.');
    }
}
