<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Inicio - Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Products/Inventory -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">{{ __('Productos en Inventario') }}</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">
                                    {{ \App\Models\Product::where('type', 'service')->count() }}
                                </p>
                            </div>
                            <div class="text-blue-500 text-4xl opacity-20">
                                <svg fill="currentColor" viewBox="0 0 20 20" class="w-12 h-12"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042L5.960 9H9a1 1 0 000 2H5.764l.86 4.3a2 2 0 11-3.97.342H2.05a1 1 0 100 2h1.972l.863 4.295a1 1 0 001.954-.26l3.099-15.545A1 1 0 009.256 2H6.993A1 1 0 006 1H3z"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Fixed Assets -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">{{ __('Activos Fijos') }}</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">
                                    {{ \App\Models\Product::where('type', 'asset')->count() }}
                                </p>
                            </div>
                            <div class="text-green-500 text-4xl opacity-20">
                                <svg fill="currentColor" viewBox="0 0 20 20" class="w-12 h-12"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a1 1 0 001 1h12a1 1 0 001-1V6a2 2 0 00-2-2H4zm0 5V6h12v3H4z" clip-rule="evenodd"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Suppliers -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">{{ __('Proveedores') }}</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">
                                    {{ \App\Models\Supplier::count() }}
                                </p>
                            </div>
                            <div class="text-orange-500 text-4xl opacity-20">
                                <svg fill="currentColor" viewBox="0 0 20 20" class="w-12 h-12"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000-2A4 4 0 000 5v10a4 4 0 004 4h12a4 4 0 004-4V5a4 4 0 00-4-4 1 1 0 000 2 2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5z" clip-rule="evenodd"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Alerts -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">{{ __('Alertas Activas') }}</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">
                                    {{ \App\Models\Alert::where('status', 'active')->count() }}
                                </p>
                            </div>
                            <div class="text-red-500 text-4xl opacity-20">
                                <svg fill="currentColor" viewBox="0 0 20 20" class="w-12 h-12"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Analytics Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Inventory by Category -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Inventario por Categoría') }}</h3>
                        <div class="space-y-2">
                            @php
                                $inventoryByCategory = \App\Models\Product::where('type', 'service')
                                    ->groupBy('category_id')
                                    ->selectRaw('category_id, COUNT(*) as count')
                                    ->with('category')
                                    ->get();
                            @endphp
                            @forelse($inventoryByCategory as $item)
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">{{ $item->category->name ?? 'Sin categoría' }}</span>
                                    <div class="flex items-center gap-2">
                                        <div class="w-32 bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ ($item->count / max(1, $inventoryByCategory->max('count'))) * 100 }}%"></div>
                                        </div>
                                        <span class="text-gray-900 font-semibold">{{ $item->count }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 text-sm">{{ __('Sin datos disponibles') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Fixed Assets by Category -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Activos Fijos por Categoría') }}</h3>
                        <div class="space-y-2">
                            @php
                                $assetsByCategory = \App\Models\Product::where('type', 'asset')
                                    ->groupBy('category_id')
                                    ->selectRaw('category_id, COUNT(*) as count')
                                    ->with('category')
                                    ->get();
                            @endphp
                            @forelse($assetsByCategory as $item)
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">{{ $item->category->name ?? 'Sin categoría' }}</span>
                                    <div class="flex items-center gap-2">
                                        <div class="w-32 bg-gray-200 rounded-full h-2">
                                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ ($item->count / max(1, $assetsByCategory->max('count'))) * 100 }}%"></div>
                                        </div>
                                        <span class="text-gray-900 font-semibold">{{ $item->count }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 text-sm">{{ __('Sin datos disponibles') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory by Warehouse -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Distribución por Almacén') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @php
                            $warehouseData = \App\Models\Product::groupBy('warehouse_id')
                                ->selectRaw('warehouse_id, COUNT(*) as total_products, SUM(quantity) as total_quantity')
                                ->with('warehouse')
                                ->get();
                        @endphp
                        @forelse($warehouseData as $data)
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <p class="text-sm font-medium text-gray-600">{{ $data->warehouse->name ?? 'Sin almacén' }}</p>
                                <p class="text-2xl font-bold text-gray-900 mt-2">{{ $data->total_products }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $data->total_quantity ?? 0 }} unidades</p>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm col-span-4">{{ __('Sin datos disponibles') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent Alerts -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Alertas Recientes') }}</h3>
                        <a href="{{ route('alerts.index') }}" class="text-blue-600 hover:text-blue-900 text-sm">{{ __('Ver todas') }}</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-2 text-left text-gray-600">{{ __('Producto') }}</th>
                                    <th class="px-4 py-2 text-left text-gray-600">{{ __('Tipo') }}</th>
                                    <th class="px-4 py-2 text-left text-gray-600">{{ __('Estado') }}</th>
                                    <th class="px-4 py-2 text-left text-gray-600">{{ __('Fecha') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse(\App\Models\Alert::latest()->limit(5)->with('product')->get() as $alert)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-gray-900">{{ $alert->product->name_item ?? '-' }}</td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 py-1 rounded text-xs font-medium {{ $alert->type === 'stock' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $alert->type }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 py-1 rounded text-xs font-medium {{ $alert->status === 'active' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                                {{ $alert->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-gray-500">{{ $alert->created_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-gray-500">{{ __('Sin alertas') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
