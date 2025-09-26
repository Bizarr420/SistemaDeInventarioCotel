<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        $warehouses = Warehouse::withCount('stocks')
            ->withSum('stocks as total_stock', 'current_stock')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        return view('warehouses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50', 'unique:warehouses,code'],
            'location' => ['nullable', 'string', 'max:150'],
        ]);

        Warehouse::create($data);

        return redirect()->route('warehouses.index')->with('ok', 'Almacén creado correctamente.');
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('warehouses', 'code')->ignore($warehouse->id),
            ],
            'location' => ['nullable', 'string', 'max:150'],
        ]);

        $warehouse->update($data);

        return redirect()->route('warehouses.index')->with('ok', 'Almacén actualizado correctamente.');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        if ($warehouse->stocks()->exists()) {
            return redirect()
                ->route('warehouses.index')
                ->with('error', 'No puedes eliminar un almacén con stock asociado.');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('ok', 'Almacén eliminado correctamente.');
    }
}
