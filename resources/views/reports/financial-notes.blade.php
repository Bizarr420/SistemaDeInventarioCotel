<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Notas Financieras SICAT - COTEL La Paz') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="rounded-xl bg-slate-900 p-4 text-white">
                            <p class="text-sm text-slate-300">Fuente documental</p>
                            <p class="text-lg font-semibold">Estados financieros 2024 y 2023</p>
                            <p class="mt-1 text-xs text-slate-400">Nota 9 incluye proyección estimada 2025–2026</p>
                        </div>
                        <div class="rounded-xl bg-amber-50 p-4 border border-amber-200">
                            <p class="text-sm text-amber-700">Relación con SICAT</p>
                            <p class="text-lg font-semibold text-amber-900">Obsolescencia, deterioro y bajas</p>
                        </div>
                        <div class="rounded-xl bg-emerald-50 p-4 border border-emerald-200">
                            <p class="text-sm text-emerald-700">Cobertura</p>
                            <p class="text-lg font-semibold text-emerald-900">Inventarios, activos fijos y depreciación</p>
                        </div>
                    </div>

                    <div class="space-y-8">
                        @foreach($notes as $note)
                            <section class="rounded-2xl border border-gray-200 overflow-hidden">
                                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                    <div class="flex flex-col gap-1 md:flex-row md:items-end md:justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">{{ $note['code'] }}</p>
                                            <h3 class="text-xl font-semibold text-gray-900">{{ $note['title'] }}</h3>
                                            <p class="text-sm text-gray-600">{{ $note['description'] }}</p>
                                        </div>
                                        <div class="flex flex-wrap gap-3 text-sm">
                                            @foreach($note['years'] as $year)
                                                <div class="rounded-lg {{ in_array($year, $note['estimated_years'], true) ? 'bg-amber-50 border-amber-200' : 'bg-white border-gray-200' }} px-4 py-2 border">
                                                    <span class="block {{ in_array($year, $note['estimated_years'], true) ? 'text-amber-700' : 'text-gray-500' }}">
                                                        {{ $year }}{{ in_array($year, $note['estimated_years'], true) ? ' (est.)' : '' }}
                                                    </span>
                                                    <span class="font-semibold text-gray-900">{{ number_format($note['totals'][$year], 2) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="overflow-x-auto bg-white">
                                    <table class="min-w-full divide-y divide-gray-200 text-gray-900">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Código</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Detalle</th>
                                                @foreach($note['years'] as $year)
                                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider {{ in_array($year, $note['estimated_years'], true) ? 'text-amber-700' : 'text-gray-500' }}">
                                                        {{ $year }}{{ in_array($year, $note['estimated_years'], true) ? ' (est.)' : '' }}
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach($note['rows'] as $row)
                                                <tr class="hover:bg-slate-50/70">
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-700">{{ $row['code'] }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $row['detail'] }}</td>
                                                    @foreach($note['years'] as $year)
                                                        @php($amount = $row['values'][$year] ?? $row[$year] ?? 0)
                                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm {{ $amount < 0 ? 'text-red-700' : 'text-gray-900' }}">{{ number_format($amount, 2) }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                            <tr class="bg-gray-50 font-semibold">
                                                <td class="px-4 py-3 text-sm text-gray-700" colspan="2">Total {{ $note['title'] }}</td>
                                                @foreach($note['years'] as $year)
                                                    <td class="px-4 py-3 text-right text-sm text-gray-900">{{ number_format($note['totals'][$year], 2) }}</td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>