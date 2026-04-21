<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Controles COSO 2013') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex space-x-2">
                <a href="{{ route('coso.controls.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Nuevo Control
                </a>
            </div>

            @if($component)
                <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded">
                    Filtrado por: <strong>{{ \App\Models\CosoControl::getComponentLabel($component) }}</strong>
                    <a href="{{ route('coso.controls.index') }}" class="underline ml-2">Limpiar filtro</a>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Componente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Objetivo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Efectividad</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($controls as $control)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ \App\Models\CosoControl::getComponentLabel($control->component) }}</td>
                                <td class="px-6 py-4 text-sm">{{ $control->control_objective }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 rounded text-xs
                                        @if($control->control_type == 'preventive') bg-green-100 text-green-800
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ ucfirst($control->control_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 rounded text-xs
                                        @if($control->status == 'active') bg-green-100 text-green-800
                                        @elseif($control->status == 'inactive') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ ucfirst($control->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $control->effectiveness ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('coso.controls.show', $control) }}" class="text-blue-600 hover:underline">Ver</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $controls->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>