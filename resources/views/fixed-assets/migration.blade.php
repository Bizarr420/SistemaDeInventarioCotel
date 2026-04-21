<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Migracion de Activos Fijos') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md bg-red-50 p-4 text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4 text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4">
                    <h3 class="text-xl font-semibold text-gray-900">Cargar archivo de migracion</h3>
                    <p class="text-sm text-gray-700">
                        Formatos permitidos: Excel (.xlsx), CSV (.csv) y PDF (.pdf). Para PDF, usa texto tabular con delimitador
                        en la primera linea (por ejemplo: nombre|categoria|proveedor|sucursal|cantidad|costo_unitario).
                    </p>
                    <p class="text-sm text-gray-700">
                        Encabezados recomendados: nombre_activo, categoria, proveedor, sucursal, piso, oficina, encargado, departamento,
                        cantidad, costo_unitario, sku, vida_util_anios, fecha_fin_vida_util, descripcion.
                    </p>
                    <div>
                        <a href="{{ asset('templates/migracion_activos_fijos_plantilla.csv') }}" class="inline-flex items-center px-4 py-2 rounded-md font-semibold text-sm shadow-sm bg-slate-800 text-white border border-slate-900 hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500 transition">
                            Descargar plantilla CSV
                        </a>
                    </div>

                    <form method="POST" action="{{ route('fixed-assets.migration.preview') }}" enctype="multipart/form-data" class="flex flex-col md:flex-row gap-3 md:items-end">
                        @csrf
                        <div class="w-full md:w-2/3">
                            <label for="migration_file" class="block text-sm font-medium text-gray-700">Archivo</label>
                            <input id="migration_file" name="migration_file" type="file" required accept=".xlsx,.csv,.pdf"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-md font-semibold text-sm shadow-sm border transition" style="background-color:#1d4ed8;color:#ffffff;border-color:#1e40af;">
                            Previsualizar
                        </button>
                    </form>
                </div>
            </div>

            @if (!empty($previewRows))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h3 class="text-xl font-semibold text-gray-900">Previsualizacion</h3>
                            @if ($previewSummary)
                                <div class="text-sm text-gray-800 font-medium">
                                    Total: <strong>{{ $previewSummary['total'] }}</strong> |
                                    Validas: <strong class="text-green-700">{{ $previewSummary['valid'] }}</strong> |
                                    Con error: <strong class="text-red-700">{{ $previewSummary['invalid'] }}</strong>
                                </div>
                            @endif
                        </div>

                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-800">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Estado</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Fila</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Nombre</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Categoria</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Proveedor</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Sucursal</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Cantidad</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Costo</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($previewRows as $row)
                                        <tr>
                                            <td class="px-3 py-2">
                                                @if ($row['is_valid'])
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-800">Valida</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-red-100 text-red-800">Error</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">{{ $row['line'] }}</td>
                                            <td class="px-3 py-2">{{ $row['name'] }}</td>
                                            <td class="px-3 py-2">{{ $row['category_name'] }}</td>
                                            <td class="px-3 py-2">{{ $row['supplier_name'] }}</td>
                                            <td class="px-3 py-2">{{ $row['location_branch'] }}</td>
                                            <td class="px-3 py-2">{{ $row['quantity'] }}</td>
                                            <td class="px-3 py-2">{{ number_format((float) $row['unit_cost'], 2) }}</td>
                                            <td class="px-3 py-2">
                                                @if (!empty($row['errors']))
                                                    <span class="text-red-700">{{ implode(' | ', $row['errors']) }}</span>
                                                @elseif (!empty($row['warnings']))
                                                    <span class="text-amber-700">{{ implode(' | ', $row['warnings']) }}</span>
                                                @else
                                                    <span class="text-gray-500">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('fixed-assets.migration.create', ['clear_preview' => 1]) }}" class="inline-flex items-center px-4 py-2 rounded-md font-semibold text-sm shadow-sm border transition" style="background-color:#334155;color:#ffffff;border-color:#1f2937;">
                                Limpiar previsualizacion
                            </a>
                            <form method="POST" action="{{ route('fixed-assets.migration.store') }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md font-semibold text-sm shadow-sm border transition" style="background-color:#15803d;color:#ffffff;border-color:#166534;">
                                    Confirmar migracion
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
