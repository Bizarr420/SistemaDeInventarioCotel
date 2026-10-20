<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Traits\AuthorizesPermission;

class SupplierController extends Controller
{
    use AuthorizesPermission;

    public function index(): View
    {
        $this->authorizeAnyPermission(['assets.view', 'inventory.view']);
        $suppliers = Supplier::orderBy('name')->paginate(15)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        $this->authorizeAnyPermission(['assets.create', 'inventory.create']);
        return view('suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAnyPermission(['assets.create', 'inventory.create']);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'contact' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
        ]);

        Supplier::create($data);

        return redirect()->route('suppliers.index')->with('ok', 'Proveedor creado correctamente.');
    }

    public function edit(Supplier $supplier): View
    {
        $this->authorizeAnyPermission(['assets.update', 'inventory.update']);
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $this->authorizeAnyPermission(['assets.update', 'inventory.update']);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'contact' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
        ]);

        $supplier->update($data);

        return redirect()->route('suppliers.index')->with('ok', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorizeAnyPermission(['assets.delete', 'inventory.delete']);
        if ($supplier->products()->exists()) {
            return redirect()
                ->route('suppliers.index')
                ->with('error', 'No puedes eliminar un proveedor con productos asociados.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('ok', 'Proveedor eliminado correctamente.');
    }
}
