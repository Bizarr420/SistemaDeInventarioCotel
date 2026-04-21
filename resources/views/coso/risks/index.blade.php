<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Evaluación de Riesgos COSO') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex space-x-2">
                <a href="{{ route('coso.risks.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Nuevo Riesgo
                </a>
                <a href="{{ route('coso.risks.dashboard') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                    Dashboard de Riesgos
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Activo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Probabilidad</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Impacto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Puntuación</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nivel</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($assessments as $assessment)
                            <tr>
                                <td class="px-6 py-4 text-sm">{{ $assessment->product->name_item }}</td>
                                <td class="px-6 py-4 text-sm">{{ $assessment->risk_category }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $assessment->probability }}/5</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $assessment->impact }}/5</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">{{ $assessment->probability * $assessment->impact }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 rounded text-xs text-white
                                        @if($assessment->risk_level == 'critical') bg-red-600
                                        @elseif($assessment->risk_level == 'high') bg-orange-600
                                        @elseif($assessment->risk_level == 'medium') bg-yellow-600
                                        @else bg-green-600 @endif">
                                        {{ ucfirst($assessment->risk_level) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ ucfirst($assessment->status) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('coso.risks.show', $assessment) }}" class="text-blue-600 hover:underline">Ver</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $assessments->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>