<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Activo Fijo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('fixed-assets.update', ['fixed_asset' => $asset->id]) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Nombre del Activo') }}</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $asset->name_item) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="sku" class="block text-sm font-medium text-gray-700">{{ __('SKU') }}</label>
                                <input type="text" name="sku" id="sku" value="{{ old('sku', $asset->sku) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('sku') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700">{{ __('Categoría') }}</label>
                                <select name="category_id" id="category_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">{{ __('Seleccionar categoría') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $asset->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="location_branch" class="block text-sm font-medium text-gray-700">{{ __('Sucursal') }}</label>
                                <input type="text" name="location_branch" id="location_branch" value="{{ old('location_branch', $asset->location_branch) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('location_branch') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="location_floor" class="block text-sm font-medium text-gray-700">{{ __('Piso') }}</label>
                                <input type="text" name="location_floor" id="location_floor" value="{{ old('location_floor', $asset->location_floor) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('location_floor') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="location_office" class="block text-sm font-medium text-gray-700">{{ __('Oficina') }}</label>
                                <input type="text" name="location_office" id="location_office" value="{{ old('location_office', $asset->location_office) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('location_office') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="assigned_to" class="block text-sm font-medium text-gray-700">{{ __('Encargado / Usuario') }}</label>
                                <input type="text" name="assigned_to" id="assigned_to" value="{{ old('assigned_to', $asset->assigned_to) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('assigned_to') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="assigned_department" class="block text-sm font-medium text-gray-700">{{ __('Departamento') }}</label>
                                <input type="text" name="assigned_department" id="assigned_department" value="{{ old('assigned_department', $asset->assigned_department) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('assigned_department') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="supplier_id" class="block text-sm font-medium text-gray-700">{{ __('Proveedor') }}</label>
                                <select name="supplier_id" id="supplier_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">{{ __('Seleccionar proveedor') }}</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id', $asset->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                @error('supplier_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Estado del Activo (segun verificacion)') }}</label>
                                <div class="mt-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm text-gray-800">
                                    {{ ucfirst($asset->asset_status ?? 'operativo') }}
                                </div>
                            </div>

                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700">{{ __('Cantidad') }}</label>
                                <input type="number" name="quantity" id="quantity" value="{{ old('quantity', $asset->quantity) }}" min="1" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('quantity') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="unit_cost" class="block text-sm font-medium text-gray-700">{{ __('Costo Unitario') }}</label>
                                <input type="number" name="unit_cost" id="unit_cost" step="0.01" value="{{ old('unit_cost', $asset->unit_cost) }}" min="0" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('unit_cost') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="useful_life_years" class="block text-sm font-medium text-gray-700">{{ __('Vida Útil (años)') }}</label>
                                <input type="number" name="useful_life_years" id="useful_life_years" value="{{ old('useful_life_years', $asset->useful_life_years) }}" min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('useful_life_years') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="expected_useful_life" class="block text-sm font-medium text-gray-700">{{ __('Fecha Esperada de Fin de Vida Útil') }}</label>
                                <input type="date" name="expected_useful_life" id="expected_useful_life" value="{{ old('expected_useful_life', $asset->expected_useful_life) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('expected_useful_life') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="obsolete_disposition_status" class="block text-sm font-medium text-gray-700">{{ __('Subestado (solo si Obsoleto)') }}</label>
                                @if(($asset->asset_status ?? 'operativo') === 'obsoleto')
                                    <select name="obsolete_disposition_status" id="obsolete_disposition_status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="pendiente" {{ old('obsolete_disposition_status', $asset->obsolete_disposition_status) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="vendido" {{ old('obsolete_disposition_status', $asset->obsolete_disposition_status) == 'vendido' ? 'selected' : '' }}>Vendido</option>
                                        <option value="destruido" {{ old('obsolete_disposition_status', $asset->obsolete_disposition_status) == 'destruido' ? 'selected' : '' }}>Destruido</option>
                                    </select>
                                @else
                                    <div class="mt-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm text-gray-600">
                                        No aplica, porque el activo no esta obsoleto.
                                    </div>
                                @endif
                                <p class="mt-1 text-xs text-gray-500">Por defecto en obsoleto: Pendiente. Luego decision gerencial: Vendido o Destruido.</p>
                                @error('obsolete_disposition_status') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Descripción') }}</label>
                            <textarea name="description" id="description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $asset->description) }}</textarea>
                            @error('description') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end gap-4">
                            <a href="{{ route('fixed-assets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                                {{ __('Cancelar') }}
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                                {{ __('Actualizar Activo') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
