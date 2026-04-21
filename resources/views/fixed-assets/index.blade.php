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

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Nombre') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Categoría') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Proveedor') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Ubicación') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Responsable / Depto') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Cantidad') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Costo Unit.') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Vida Útil') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Estado') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($assets as $asset)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $asset->name_item }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asset->category->name ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asset->supplier->name ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $asset->location_branch ?? '-' }}
                                        @if($asset->location_floor) / Piso: {{ $asset->location_floor }} @endif
                                        @if($asset->location_office) / Oficina: {{ $asset->location_office }} @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $asset->assigned_to ?? '-' }}
                                        @if($asset->assigned_department) ({{ $asset->assigned_department }}) @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asset->quantity ?? 0 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($asset->unit_cost ?? 0, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asset->useful_life_years ? $asset->useful_life_years . ' años' : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @php($assetStatus = $asset->asset_status ?? 'operativo')
                                        @if($assetStatus === 'obsoleto')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-red-100 text-red-800 mr-2">Obsoleto</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-100 text-amber-800">{{ ucfirst($asset->obsolete_disposition_status ?? 'pendiente') }}</span>
                                        @elseif($assetStatus === 'deteriorado')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-100 text-amber-800">Deteriorado</span>
                                        @elseif($assetStatus === 'falla')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-orange-100 text-orange-800">Con fallas</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-800">Operativo</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="{{ route('fixed-assets.verifications.create', ['asset_id' => $asset->id]) }}" class="asset-action-link">{{ __('Verificar') }}</a>
                                        <a href="{{ route('fixed-assets.edit', $asset) }}" class="asset-action-link">{{ __('Editar') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500">{{ __('No hay activos fijos') }}</td>
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
</x-app-layout>
