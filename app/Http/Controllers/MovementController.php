<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovementRequest;
use App\Models\Movement;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MovementController extends Controller
{
    public function index(): View
    {
        return view('movements.index', [
            'movements' => Movement::with(['product', 'warehouse', 'user'])->latest()->paginate(20),
            'products' => Product::orderBy('name_item')->get(),
            'warehouses' => Warehouse::orderBy('name')->get(),
        ]);
    }

    public function store(StoreMovementRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $stock = ProductStock::firstOrCreate(
                [
                    'product_id' => $data['product_id'],
                    'warehouse_id' => $data['warehouse_id'],
                ],
                ['current_stock' => 0]
            );

            if ($data['type'] === 'out' && $stock->current_stock < $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stock insuficiente en el almacén seleccionado.',
                ]);
            }

            $data['type'] === 'in'
                ? $stock->increment('current_stock', $data['quantity'])
                : $stock->decrement('current_stock', $data['quantity']);

            Movement::create([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'type' => $data['type'],
                'quantity' => $data['quantity'],
                'note' => $data['note'] ?? null,
                'user_id' => (int) auth()->id(),
            ]);
        });

        return redirect()
            ->route('movements.index')
            ->with('ok', 'Movimiento registrado correctamente.');
    }
}
