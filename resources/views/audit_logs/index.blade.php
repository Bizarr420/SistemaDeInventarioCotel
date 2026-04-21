<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Logs de Auditoría') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <form method="GET" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="auditable_type" class="block text-sm font-medium text-gray-700">Tipo de Modelo</label>
                            <select name="auditable_type" id="auditable_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Todos</option>
                                <option value="App\Models\Product" {{ request('auditable_type') == 'App\Models\Product' ? 'selected' : '' }}>Producto</option>
                                <option value="App\Models\Alert" {{ request('auditable_type') == 'App\Models\Alert' ? 'selected' : '' }}>Alerta</option>
                                <option value="App\Models\Dictamen" {{ request('auditable_type') == 'App\Models\Dictamen' ? 'selected' : '' }}>Dictamen</option>
                                <option value="App\Models\ObsoletenessCriterion" {{ request('auditable_type') == 'App\Models\ObsoletenessCriterion' ? 'selected' : '' }}>Criterio</option>
                            </select>
                        </div>
                        <div>
                            <label for="event" class="block text-sm font-medium text-gray-700">Evento</label>
                            <select name="event" id="event" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Todos</option>
                                <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Creado</option>
                                <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Actualizado</option>
                                <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Eliminado</option>
                            </select>
                        </div>
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700">Desde</label>
                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700">Hasta</label>
                            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="md:col-span-4">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Filtrar</button>
                            <a href="{{ route('audit_logs.index') }}" class="ml-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Limpiar</a>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modelo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cambios</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($logs as $log)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $log->user ? $log->user->name : 'Sistema' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ class_basename($log->auditable_type) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $log->auditable_id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($log->event == 'created') bg-green-100 text-green-800
                                            @elseif($log->event == 'updated') bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ ucfirst($log->event) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        @if($log->old_values || $log->new_values)
                                            <details>
                                                <summary class="cursor-pointer">Ver cambios</summary>
                                                @if($log->old_values)
                                                    <strong>Anterior:</strong> <pre class="text-xs">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                                @endif
                                                @if($log->new_values)
                                                    <strong>Nuevo:</strong> <pre class="text-xs">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                                @endif
                                            </details>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $logs->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>