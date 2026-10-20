<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Traits\AuthorizesPermission;

class CategoryController extends Controller
{
    use AuthorizesPermission;

    public function index(): View
    {
        $this->authorizeAnyPermission(['assets.view', 'inventory.view']);
        $categories = Category::orderBy('name')->paginate(15)->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        $this->authorizeAnyPermission(['assets.create', 'inventory.create']);
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAnyPermission(['assets.create', 'inventory.create']);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        Category::create($data);

        return redirect()->route('categories.index')->with('ok', 'Categoría creada correctamente.');
    }

    public function edit(Category $category): View
    {
        $this->authorizeAnyPermission(['assets.update', 'inventory.update']);
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->authorizeAnyPermission(['assets.update', 'inventory.update']);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:categories,name,' . $category->id],
            'description' => ['nullable', 'string'],
        ]);

        $category->update($data);

        return redirect()->route('categories.index')->with('ok', 'Categoría actualizada correctamente.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorizeAnyPermission(['assets.delete', 'inventory.delete']);
        if ($category->products()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'No puedes eliminar una categoría con productos asociados.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('ok', 'Categoría eliminada correctamente.');
    }
}
