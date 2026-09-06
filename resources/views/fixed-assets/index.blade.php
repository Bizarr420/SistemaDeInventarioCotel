<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Activos Fijos (Computadoras, Equipos, etc.)') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('fixed-assets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800 transition ease-in-out duration-150">
                    {{ __('Listado') }}
                </a>
                <a href="{{ route('fixed-assets.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                    {{ __('Crear') }}
                </a>
                <a href="{{ route('fixed-assets.migration.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition ease-in-out duration-150">
                    {{ __('Migracion') }}
                </a>
                <a href="{{ route('fixed-assets.verifications.index') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition ease-in-out duration-150">
                    {{ __('Verificacion') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full max-w-[1600px] mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                {{ session('success') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <form method="GET" action="{{ route('fixed-assets.index') }}" class="mb-4 rounded-lg bg-white p-4 shadow-sm">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-base font-semibold text-gray-900">{{ __('Filtros avanzados') }}</h3>
                    <a href="{{ route('fixed-assets.index') }}" class="text-sm font-semibold text-orange-700 hover:underline">{{ __('Limpiar filtros') }}</a>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Buscar activo, SKU...') }}" class="rounded-md border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500 xl:col-span-2">
                    <select name="category_id" class="rounded-md border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                        <option value="">{{ __('Categoría') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <select name="supplier_id" class="rounded-md border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                        <option value="">{{ __('Proveedor') }}</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(($filters['supplier_id'] ?? '') == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    <input type="search" name="branch" value="{{ $filters['branch'] ?? '' }}" placeholder="{{ __('Sucursal') }}" class="rounded-md border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    <input type="search" name="location" value="{{ $filters['location'] ?? '' }}" placeholder="{{ __('Ubicación') }}" class="rounded-md border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    <input type="search" name="assignee" value="{{ $filters['assignee'] ?? '' }}" placeholder="{{ __('Departamento / responsable') }}" class="rounded-md border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    <select name="asset_status" class="rounded-md border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                        <option value="">{{ __('Estado') }}</option>
                        @foreach(['operativo' => 'Operativo', 'falla' => 'Con fallas', 'deteriorado' => 'Deteriorado', 'obsoleto' => 'Obsoleto'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['asset_status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="substatus" class="rounded-md border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                        <option value="">{{ __('Subestado') }}</option>
                        @foreach(['pendiente' => 'Pendiente', 'vendido' => 'Vendido', 'destruido' => 'Destruido'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['substatus'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="min_value" value="{{ $filters['min_value'] ?? '' }}" min="0" step="0.01" placeholder="{{ __('Valor mínimo') }}" class="rounded-md border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    <input type="number" name="max_value" value="{{ $filters['max_value'] ?? '' }}" min="0" step="0.01" placeholder="{{ __('Valor máximo') }}" class="rounded-md border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    <input type="number" name="min_age" value="{{ $filters['min_age'] ?? '' }}" min="0" placeholder="{{ __('Antigüedad mínima (años)') }}" class="rounded-md border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    <input type="number" name="max_age" value="{{ $filters['max_age'] ?? '' }}" min="0" placeholder="{{ __('Antigüedad máxima (años)') }}" class="rounded-md border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    <input type="number" name="min_remaining_life" value="{{ $filters['min_remaining_life'] ?? '' }}" min="0" max="100" placeholder="{{ __('Vida útil restante mínima (%)') }}" class="rounded-md border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    <input type="number" name="max_remaining_life" value="{{ $filters['max_remaining_life'] ?? '' }}" min="0" max="100" placeholder="{{ __('Vida útil restante máxima (%)') }}" class="rounded-md border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    <select name="verification_status" class="rounded-md border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                        <option value="">{{ __('Estado de verificación') }}</option>
                        <option value="verified" @selected(($filters['verification_status'] ?? '') === 'verified')>{{ __('Verificado') }}</option>
                        <option value="pending" @selected(($filters['verification_status'] ?? '') === 'pending')>{{ __('Pendiente') }}</option>
                    </select>
                    <select name="sort" class="rounded-md border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                        @foreach(['name' => 'Nombre', 'value' => 'Valor', 'age' => 'Antigüedad', 'acquisition_date' => 'Fecha de adquisición', 'status' => 'Estado', 'remaining_life' => 'Vida útil restante'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'name') === $value)>{{ __('Ordenar por: ') . $label }}</option>
                        @endforeach
                    </select>
                    <select name="direction" class="rounded-md border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                        <option value="asc" @selected(($filters['direction'] ?? 'asc') === 'asc')>{{ __('Ascendente') }}</option>
                        <option value="desc" @selected(($filters['direction'] ?? '') === 'desc')>{{ __('Descendente') }}</option>
                    </select>
                    <button type="submit" class="rounded-md px-4 py-2 text-sm font-semibold">{{ __('Aplicar filtros') }}</button>
                </div>
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg asset-table-container">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-4 py-3">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">{{ __('Listado de activos') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('Las columnas secundarias se ocultan automáticamente en pantallas pequeñas.') }}</p>
                    </div>
                    <details class="relative">
                        <summary class="cursor-pointer rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            {{ __('Columnas') }}
                        </summary>
                        <fieldset class="absolute right-0 z-10 mt-2 w-56 rounded-md border border-gray-200 bg-white p-3 shadow-lg">
                            <legend class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Mostrar columnas') }}</legend>
                            @foreach([
                                'category' => __('Categoría'),
                                'supplier' => __('Proveedor'),
                                'location' => __('Ubicación'),
                                'assignee' => __('Responsable / Depto'),
                                'useful-life' => __('Vida útil'),
                            ] as $column => $label)
                                <label class="flex items-center gap-2 py-1 text-sm text-gray-700">
                                    <input type="checkbox" class="asset-column-toggle rounded border-gray-300 text-orange-600 focus:ring-orange-500" data-column="{{ $column }}" checked>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </fieldset>
                    </details>
                </div>
                <div class="overflow-x-auto">
                    <table class="asset-table w-full border-collapse">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Activo') }}</th>
                                <th data-column="category" class="asset-column-secondary px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Categoría') }}</th>
                                <th data-column="supplier" class="asset-column-secondary px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Proveedor') }}</th>
                                <th data-column="location" class="asset-column-secondary px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Ubicación') }}</th>
                                <th data-column="assignee" class="asset-column-secondary px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Responsable / Depto') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Cantidad') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Valor') }}</th>
                                <th data-column="useful-life" class="asset-column-secondary px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Vida Útil') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Estado') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Subestado') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Verificación') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($assets as $asset)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('fixed-assets.show', $asset) }}" class="asset-name-link">{{ $asset->name_item }}</a>
                                    </td>
                                    <td data-column="category" class="asset-column-secondary px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asset->category->name ?? '-' }}</td>
                                    <td data-column="supplier" class="asset-column-secondary px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asset->supplier->name ?? '-' }}</td>
                                    <td data-column="location" class="asset-column-secondary px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $asset->location_branch ?? '-' }}
                                        @if($asset->location_floor) / Piso: {{ $asset->location_floor }} @endif
                                        @if($asset->location_office) / Oficina: {{ $asset->location_office }} @endif
                                    </td>
                                    <td data-column="assignee" class="asset-column-secondary px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $asset->assigned_to ?? '-' }}
                                        @if($asset->assigned_department) ({{ $asset->assigned_department }}) @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asset->quantity ?? 0 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($asset->unit_cost ?? 0, 2) }}</td>
                                    <td data-column="useful-life" class="asset-column-secondary px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asset->useful_life_years ? $asset->useful_life_years . ' años' : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @php($assetStatus = $asset->asset_status ?? 'operativo')
                                        @if($assetStatus === 'obsoleto')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-red-100 text-red-800">Obsoleto</span>
                                        @elseif($assetStatus === 'deteriorado')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-100 text-amber-800">Deteriorado</span>
                                        @elseif($assetStatus === 'falla')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-orange-100 text-orange-800">Con fallas</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-800">Operativo</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($assetStatus === 'obsoleto')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-100 text-amber-800">{{ ucfirst($asset->obsolete_disposition_status ?? 'pendiente') }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($asset->latestVerification)
                                            {{ optional($asset->latestVerification->verified_at)->format('d/m/Y') }}
                                        @else
                                            <span class="text-gray-400">{{ __('Pendiente') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('fixed-assets.verifications.create', ['asset_id' => $asset->id]) }}" class="asset-action-button asset-action-button-primary">{{ __('Verificar') }}</a>
                                            <a href="{{ route('fixed-assets.edit', $asset) }}" class="asset-action-button asset-action-button-secondary">{{ __('Editar') }}</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="px-6 py-4 text-center text-sm text-gray-500">{{ __('No hay activos fijos') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $assets->links() }}
                </div>
            </div>
        </div>
    </div>
    <script>
        const assetTableContainer = document.querySelector('.asset-table-container');
        const assetColumnToggles = document.querySelectorAll('.asset-column-toggle');

        const setAssetColumnVisibility = (toggle) => {
            const column = toggle.dataset.column;

            assetTableContainer.querySelectorAll(`[data-column="${column}"]`).forEach((cell) => {
                cell.classList.toggle('asset-column-hidden', !toggle.checked);
            });
        };

        assetColumnToggles.forEach((toggle) => {
            if (window.matchMedia('(max-width: 1279px)').matches) {
                toggle.checked = false;
                setAssetColumnVisibility(toggle);
            }

            toggle.addEventListener('change', () => {
                assetTableContainer.classList.add('has-custom-columns');
                setAssetColumnVisibility(toggle);
            });
        });
    </script>
</x-app-layout>
