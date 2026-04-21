<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nuevo Producto de Inventario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('inventory.store') }}" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Nombre del Producto') }}</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="sku" class="block text-sm font-medium text-gray-700">{{ __('SKU') }}</label>
                                <input type="text" name="sku" id="sku" value="{{ old('sku') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('sku') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700">{{ __('Categoría') }}</label>
                                <select name="category_id" id="category_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">{{ __('Seleccionar categoría') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="warehouse_id" class="block text-sm font-medium text-gray-700">{{ __('Almacén') }}</label>
                                <select name="warehouse_id" id="warehouse_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">{{ __('Seleccionar almacén') }}</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                                @error('warehouse_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="supplier_id" class="block text-sm font-medium text-gray-700">{{ __('Proveedor') }}</label>
                                <select name="supplier_id" id="supplier_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">{{ __('Seleccionar proveedor') }}</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                @error('supplier_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700">{{ __('Cantidad') }}</label>
                                <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 0) }}" min="0" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('quantity') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="unit_cost" class="block text-sm font-medium text-gray-700">{{ __('Costo Unitario') }}</label>
                                <input type="number" name="unit_cost" id="unit_cost" step="0.01" value="{{ old('unit_cost', 0) }}" min="0" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('unit_cost') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Descripción') }}</label>
                            <textarea name="description" id="description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                            @error('description') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end gap-4">
                            <a href="{{ route('inventory.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                                {{ __('Cancelar') }}
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                                {{ __('Crear Producto') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
