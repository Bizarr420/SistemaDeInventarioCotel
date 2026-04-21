<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Reporte Comparativo Valor Tecnico vs Contable - Activos Fijos SICAT') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="min-w-full divide-y divide-gray-200 text-gray-900">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activo Fijo (SICAT)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Técnico</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Contable</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diferencia</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($products as $product)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $product->name_item }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($product->technical_value, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($product->current_accounting_value, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($product->technical_value - $product->current_accounting_value, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>