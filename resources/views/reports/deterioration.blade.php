<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Reporte de Deterioro - Activos Fijos SICAT') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4 flex space-x-2">
                        <a href="{{ route('reports.obsolescence.export', ['format' => 'pdf']) }}" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Exportar PDF
                        </a>
                        <a href="{{ route('reports.obsolescence.export', ['format' => 'excel']) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Exportar Excel
                        </a>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 text-gray-900">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activo Fijo (SICAT)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vida Útil (%)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deterioro</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Desviación Patrimonial</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($products as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $item['product']->name_item }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($item['useful_life_percentage'], 2) }}%</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($item['deterioration'], 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($item['patrimonial_deviation'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>