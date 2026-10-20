<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Traits\AuthorizesPermission;

class WarehouseController extends Controller
{
    use AuthorizesPermission;

    public function index(): View
    {
        $this->authorizePermission('inventory.view');
        $warehouses = Warehouse::withCount('stocks')
            ->withSum('stocks as total_stock', 'current_stock')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        $this->authorizePermission('inventory.create');
        return view('warehouses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission('inventory.create');
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
        $this->authorizePermission('inventory.update');
        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $this->authorizePermission('inventory.update');
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
        $this->authorizePermission('inventory.delete');
        if ($warehouse->stocks()->exists()) {
            return redirect()
                ->route('warehouses.index')
                ->with('error', 'No puedes eliminar un almacén con stock asociado.');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('ok', 'Almacén eliminado correctamente.');
    }
}
