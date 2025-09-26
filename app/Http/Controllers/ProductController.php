<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\ProductStock;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Listado con búsqueda y paginación.
     */
    public function index()
    {
        $q = request('q');

        $items = Product::with(['stocks.warehouse', 'category', 'supplier'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($x) use ($q) {
                    $x->where('internal_code', 'like', "%{$q}%")
                      ->orWhere('part_number', 'like', "%{$q}%")
                      ->orWhere('item', 'like', "%{$q}%")
                      ->orWhere('name_item', 'like', "%{$q}%")
                      ->orWhere('mac', 'like', "%{$q}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('products.index', compact('items', 'q'));
    }

    /**
     * Form de creación.
     */
    public function create()
    {
        return view('products.create', [
            'categories' => Category::orderBy('name')->get(),
            'suppliers'  => Supplier::orderBy('name')->get(),
            'warehouses' => Warehouse::orderBy('name')->get(), // para stock inicial por almacén
        ]);
    }

    /**
     * Guardar producto (y opcionalmente stock inicial en un almacén).
     */
    // en ProductController
    public function store(Request $request)
    {
        $data = $request->validate([
            'internal_code' => 'nullable|string|max:100|unique:products,internal_code',
            'part_number'   => 'nullable|string|max:120',
            'item'          => 'nullable|string|max:120',
            'name_item'     => 'required|string|max:150',
            'cnd'           => 'nullable|string|max:80',
            'unit'          => 'nullable|string|max:20',
            'mac'           => 'nullable|string|max:50',
            'description'   => 'nullable|string',
            'note'          => 'nullable|string|max:500',
            'category_id'   => 'nullable|exists:categories,id',
            'supplier_id'   => 'nullable|exists:suppliers,id',
            'warehouse_id'  => 'nullable|exists:warehouses,id',
            'initial_stock' => 'nullable|integer|min:0',
        ]);

        $product = \App\Models\Product::create([
            'internal_code' => $data['internal_code'] ?? null,
            'part_number'   => $data['part_number']   ?? null,
            'item'          => $data['item']          ?? null,
            'name_item'     => $data['name_item'],
            'cnd'           => $data['cnd']           ?? null,
            'unit'          => $data['unit']          ?? null,
            'mac'           => $data['mac']           ?? null,
            'description'   => $data['description']   ?? null,
            'note'          => $data['note']          ?? null,
            'category_id'   => $data['category_id']   ?? null,
            'supplier_id'   => $data['supplier_id']   ?? null,
        ]);

        if (!empty($data['warehouse_id']) && !empty($data['initial_stock'])) {
            \App\Models\ProductStock::updateOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $data['warehouse_id']],
                ['current_stock' => (int)$data['initial_stock']]
            );
        }

        return redirect()->route('products.index')->with('ok','Producto creado');
    }

    /**
     * Mostrar detalle (si lo necesitas).
     */
    public function show(Product $product)
    {
        $product->load(['stocks.warehouse','category','supplier']);
        return view('products.show', compact('product'));
    }

    /**
     * Form de edición.
     */
    public function edit(Product $product)
    {
        return view('products.edit', [
            'product'    => $product,
            'categories' => Category::orderBy('name')->get(),
            'suppliers'  => Supplier::orderBy('name')->get(),
        ]);
    }

    /**
     * Actualizar producto.
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'internal_code' => 'nullable|string|max:100|unique:products,internal_code,' . $product->id,
            'part_number'   => 'nullable|string|max:120',
            'item'          => 'nullable|string|max:120',
            'name_item'     => 'required|string|max:150',
            'cnd'           => 'nullable|string|max:80',
            'unit'          => 'nullable|string|max:20',
            'mac'           => 'nullable|string|max:50',
            'description'   => 'nullable|string',
            'note'          => 'nullable|string|max:500',
            'category_id'   => 'nullable|exists:categories,id',
            'supplier_id'   => 'nullable|exists:suppliers,id',
        ]);

        $product->update($data);

        return redirect()->route('products.index')->with('ok', 'Producto actualizado');
    }

    /**
     * Eliminar producto.
     */
    public function destroy(Product $product)
    {
        // Si usas policies:
        // $this->authorize('delete', $product);

        $product->delete();
        return redirect()->route('products.index')->with('ok', 'Producto eliminado');
    }
}
