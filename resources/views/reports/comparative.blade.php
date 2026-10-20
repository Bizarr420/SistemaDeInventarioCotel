<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Reporte comparativo: valor técnico vs valor de ficha - Activos fijos SICAT') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="mb-4 rounded border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900">El valor contable mostrado es el valor registrado en la ficha, no el saldo del ledger ni un importe oficial.</p>
                    <table class="min-w-full divide-y divide-gray-200 text-gray-900">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activo Fijo (SICAT)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Técnico</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor de ficha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diferencia técnica / ficha</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($products as $product)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $product->name_item }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">Bs {{ number_format($product->technical_value, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">Bs {{ number_format($product->current_accounting_value, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">Bs {{ number_format($product->technical_value - $product->current_accounting_value, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>