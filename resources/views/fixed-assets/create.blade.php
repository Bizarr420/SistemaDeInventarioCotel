<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nuevo Activo Fijo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('fixed-assets.store') }}" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Nombre del Activo') }}</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="sku" class="block text-sm font-medium text-gray-700">{{ __('SKU (opcional)') }}</label>
                                <input type="text" name="sku" id="sku" value="{{ old('sku') }}" placeholder="Ej: AF-PC-001" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                <p class="mt-1 text-xs text-gray-500">Si lo dejas vacío, el sistema lo genera automáticamente.</p>
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
                                <label for="location_branch" class="block text-sm font-medium text-gray-700">{{ __('Sucursal') }}</label>
                                <input type="text" name="location_branch" id="location_branch" value="{{ old('location_branch') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('location_branch') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="location_floor" class="block text-sm font-medium text-gray-700">{{ __('Piso') }}</label>
                                <input type="text" name="location_floor" id="location_floor" value="{{ old('location_floor') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('location_floor') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="location_office" class="block text-sm font-medium text-gray-700">{{ __('Oficina') }}</label>
                                <input type="text" name="location_office" id="location_office" value="{{ old('location_office') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('location_office') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="assigned_to" class="block text-sm font-medium text-gray-700">{{ __('Encargado / Usuario') }}</label>
                                <input type="text" name="assigned_to" id="assigned_to" value="{{ old('assigned_to') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('assigned_to') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="assigned_department" class="block text-sm font-medium text-gray-700">{{ __('Departamento') }}</label>
                                <input type="text" name="assigned_department" id="assigned_department" value="{{ old('assigned_department') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('assigned_department') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
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

                            <div class="md:col-span-2 rounded-md bg-blue-50 border border-blue-200 p-3 text-sm text-blue-800">
                                Estado inicial del activo: <strong>Operativo</strong>. Los cambios a Falla, Deteriorado u Obsoleto se realizan desde Verificaciones.
                            </div>

                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700">{{ __('Cantidad') }}</label>
                                <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" min="1" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('quantity') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="unit_cost" class="block text-sm font-medium text-gray-700">{{ __('Costo Unitario') }}</label>
                                <input type="number" name="unit_cost" id="unit_cost" step="0.01" value="{{ old('unit_cost', 0) }}" min="0" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('unit_cost') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="useful_life_years" class="block text-sm font-medium text-gray-700">{{ __('Vida Útil (años)') }}</label>
                                <input type="number" name="useful_life_years" id="useful_life_years" value="{{ old('useful_life_years') }}" min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('useful_life_years') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="expected_useful_life" class="block text-sm font-medium text-gray-700">{{ __('Fecha Esperada de Fin de Vida Útil') }}</label>
                                <input type="date" name="expected_useful_life" id="expected_useful_life" value="{{ old('expected_useful_life') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('expected_useful_life') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Descripción') }}</label>
                            <textarea name="description" id="description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                            @error('description') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end gap-4">
                            <a href="{{ route('fixed-assets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                                {{ __('Cancelar') }}
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                                {{ __('Crear Activo') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
