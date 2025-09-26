@extends('layouts.app')

@section('title', 'Agregar Producto')

@section('content')
<div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg p-6">
    <h1 class="text-2xl font-bold text-gray-700 mb-6">Agregar Producto</h1>

    {{-- Errores globales --}}
    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-300 bg-red-50 p-3 text-red-700">
            <p class="font-semibold mb-2">Corrige los siguientes campos:</p>
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('products.store') }}" novalidate>
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- COD.INT. --}}
            <div>
                <label for="internal_code" class="block text-sm font-medium text-gray-700">COD.INT.</label>
                <input id="internal_code" name="internal_code" type="text"
                       value="{{ old('internal_code') }}"
                       class="mt-1 block w-full rounded-md text-gray-900 border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                @error('internal_code') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Nro de parte --}}
            <div>
                <label for="part_number" class="block text-sm font-medium text-gray-700">Nro de parte</label>
                <input id="part_number" name="part_number" type="text"
                       value="{{ old('part_number') }}"
                       class="mt-1 block w-full rounded-md text-gray-900 border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                @error('part_number') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Item --}}
            <div>
                <label for="item" class="block text-sm font-medium text-gray-700">Item</label>
                <input id="item" name="item" type="text"
                       value="{{ old('item') }}"
                       class="mt-1 block w-full rounded-md text-gray-900 border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                @error('item') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Nombre de Item (requerido) --}}
            <div>
                <label for="name_item" class="block text-sm font-medium text-gray-700">Nombre de Ítem</label>
                <input id="name_item" name="name_item" type="text" required
                       value="{{ old('name_item') }}"
                       class="mt-1 block w-full rounded-md text-gray-900 border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                @error('name_item') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- CND --}}
            <div>
                <label for="cnd" class="block text-sm font-medium text-gray-700">CND</label>
                <input id="cnd" name="cnd" type="text"
                       value="{{ old('cnd') }}"
                       class="mt-1 block w-full rounded-md text-gray-900 border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                @error('cnd') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- UND --}}
            <div>
                <label for="unit" class="block text-sm font-medium text-gray-700">Und</label>
                <input id="unit" name="unit" type="text"
                       value="{{ old('unit') }}"
                       class="mt-1 block w-full rounded-md text-gray-900 border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                @error('unit') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- MAC --}}
            <div>
                <label for="mac" class="block text-sm font-medium text-gray-700">Código MAC</label>
                <input id="mac" name="mac" type="text"
                       value="{{ old('mac') }}"
                       class="mt-1 block w-full rounded-md text-gray-900 border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                @error('mac') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Categoría (opcional) --}}
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700">Categoría</label>
                <select id="category_id" name="category_id"
                        class="mt-1 block w-full rounded-md border-gray-300 text-gray-900 bg-white shadow-sm focus:border-orange-500 focus:ring-orange-500">
                    <option value="">-- Seleccionar --</option>
                    @foreach(\App\Models\Category::orderBy('name')->get() as $c)
                        <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Proveedor (opcional) --}}
            <div>
                <label for="supplier_id" class="block text-sm font-medium text-gray-700">Proveedor</label>
                <select id="supplier_id" name="supplier_id"
                        class="mt-1 block w-full rounded-md border-gray-300 text-gray-900 bg-white shadow-sm focus:border-orange-500 focus:ring-orange-500">
                    <option value="">-- Seleccionar --</option>
                    @foreach(\App\Models\Supplier::orderBy('name')->get() as $s)
                        <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
                @error('supplier_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Descripción (full) --}}
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea id="description" name="description" rows="3"
                          class="mt-1 block w-full rounded-md text-gray-900 border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">{{ old('description') }}</textarea>
                @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Observación (nota) --}}
            <div class="md:col-span-2">
                <label for="note" class="block text-sm font-medium text-gray-700">Observación</label>
                <input id="note" name="note" type="text"
                       value="{{ old('note') }}"
                       class="mt-1 block w-full rounded-md text-gray-900 border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                @error('note') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Stock inicial opcional por almacén --}}
            <div>
                <label for="warehouse_id" class="block text-sm font-medium text-gray-700">Almacén (stock inicial)</label>
                <select id="warehouse_id" name="warehouse_id"
                        class="mt-1 block w-full rounded-md text-gray-900 border-gray-300 bg-white shadow-sm focus:border-orange-500 focus:ring-orange-500">
                    <option value="">-- Ninguno --</option>
                    @foreach(\App\Models\Warehouse::orderBy('name')->get() as $w)
                        <option value="{{ $w->id }}" @selected(old('warehouse_id') == $w->id)>{{ $w->code }} - {{ $w->name }}</option>
                    @endforeach
                </select>
                @error('warehouse_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="initial_stock" class="block text-sm font-medium text-gray-700">Stock inicial</label>
                <input id="initial_stock" name="initial_stock" type="number" min="0"
                       value="{{ old('initial_stock') }}"
                       class="mt-1 block w-full rounded-md text-gray-900 border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                @error('initial_stock') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <a href="{{ route('products.index') }}" class="mr-3 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancelar</a>
            <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700">Guardar</button>
        </div>
    </form>
</div>
@endsection
