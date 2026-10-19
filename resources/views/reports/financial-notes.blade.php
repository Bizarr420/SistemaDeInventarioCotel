<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                {{ __('Notas Financieras SICAT - COTEL La Paz') }}
            </h2>
            <button type="button" onclick="window.print()" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white print:hidden">
                Imprimir reporte
            </button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm print:hidden">
                <form method="GET" action="{{ route('reports.financial-notes') }}" class="flex flex-wrap items-end gap-4">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Gestión a consultar</span>
                        <select name="management" class="rounded-md border-slate-300 text-sm">
                            <option value="all" @selected($selectedManagement === 'all')>Todas las gestiones</option>
                            @foreach(['2023', '2024', '2025', '2026'] as $year)
                                <option value="{{ $year }}" @selected($selectedManagement === $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Fecha de corte 2026</span>
                        <input type="date" name="cutoff_date" min="2026-01-01" max="{{ now()->toDateString() }}" value="{{ $cutoffDate }}" class="rounded-md border-slate-300 text-sm">
                    </label>
                    <button type="submit" class="rounded bg-blue-700 px-4 py-2 text-sm font-semibold text-white">Consultar</button>
                </form>
                <p class="mt-3 text-xs text-slate-500">
                    Reporte generado {{ now()->format('d/m/Y') }}. El corte 2026 aplica al saldo de apertura del escenario y a los movimientos/depreciaciones con fecha efectiva hasta la fecha seleccionada.
                </p>
            </section>

            <section class="rounded-xl border border-amber-300 bg-amber-50 p-5 text-sm text-amber-950">
                <h3 class="font-semibold">Escenario de simulación SICAT</h3>
                <p class="mt-1">
                    Las cuentas 2023–2024 se distribuyeron por activo proporcionalmente a los saldos originales archivados para reproducir los totales históricos de referencia. Depreciación y perfiles también son simulados. Ningún valor de esta pantalla acredita conciliación oficial con el mayor contable.
                </p>
            </section>

            <div class="space-y-8">
                @foreach($notes as $note)
                    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                            <div class="flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ $note['code'] }}</p>
                                    <h3 class="text-xl font-semibold text-gray-900">{{ $note['title'] }}</h3>
                                    <p class="text-sm text-gray-600">{{ $note['description'] }}</p>
                                    @if(isset($note['cutoff_date']))
                                        <p class="mt-1 text-xs font-medium text-slate-600">Corte solicitado: {{ \Illuminate\Support\Carbon::parse($note['cutoff_date'])->format('d/m/Y') }}</p>
                                    @endif
                                    @if(isset($note['recorded_through_date']))
                                        <p class="mt-1 text-xs font-medium text-slate-600">
                                            Último registro efectivo disponible al corte:
                                            {{ $note['recorded_through_date'] ? \Illuminate\Support\Carbon::parse($note['recorded_through_date'])->format('d/m/Y') : 'sin registros fechados' }}
                                        </p>
                                    @endif
                                </div>
                                <div class="flex flex-wrap gap-3 text-sm">
                                    @foreach($note['years'] as $year)
                                        <div class="rounded-lg border {{ in_array($year, $note['estimated_years'] ?? [], true) ? 'border-amber-200 bg-amber-50' : 'border-gray-200 bg-white' }} px-4 py-2">
                                            <span class="block text-gray-500">
                                                {{ $year === '2026' && isset($note['cutoff_date']) ? '2026 al corte' : $year }}
                                                @if(in_array($year, $note['estimated_years'] ?? [], true)) (estimado) @endif
                                            </span>
                                            <span class="font-semibold text-gray-900">
                                                @if($note['totals'][$year] !== null)
                                                    Bs {{ number_format((float) $note['totals'][$year], 2) }}
                                                @else
                                                    Sin dato
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 p-6">
                            @if(isset($note['source_warning']))
                                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                                    {{ $note['source_warning'] }}
                                </div>
                            @endif

                            @if(isset($note['sign_warning']))
                                <div class="rounded-lg border border-sky-200 bg-sky-50 p-4 text-sm text-sky-950">
                                    {{ $note['sign_warning'] }}
                                </div>
                            @endif

                            @if(isset($note['movement_warning']))
                                <p class="text-xs text-slate-600">{{ $note['movement_warning'] }}</p>
                            @endif

                            @if(isset($note['recorded_through_date']) && ($note['recorded_through_date'] === null || $note['recorded_through_date'] < $note['cutoff_date']))
                                <div class="rounded-lg border border-orange-200 bg-orange-50 p-4 text-sm text-orange-950">
                                    La información efectiva disponible llega hasta
                                    {{ $note['recorded_through_date'] ? \Illuminate\Support\Carbon::parse($note['recorded_through_date'])->format('d/m/Y') : 'ninguna fecha registrada' }},
                                    no hasta el corte solicitado.
                                </div>
                            @endif

                            @if(isset($note['undated_2026_count']) && $note['undated_2026_count'] > 0)
                                <div class="rounded-lg border border-orange-200 bg-orange-50 p-4 text-sm text-orange-950">
                                    La base contiene {{ number_format($note['undated_2026_count']) }} registros anuales de 2026 sin fecha de corte
                                    (suma Bs {{ number_format((float) $note['undated_2026_total'], 2) }}).
                                    No se presentan como saldo al {{ \Illuminate\Support\Carbon::parse($note['cutoff_date'])->format('d/m/Y') }}.
                                </div>
                            @endif

                            @if(isset($note['future_2026_count']) && $note['future_2026_count'] > 0)
                                <div class="rounded-lg border border-sky-200 bg-sky-50 p-4 text-sm text-sky-950">
                                    Se excluyen {{ number_format($note['future_2026_count']) }} valuaciones fechadas después del corte seleccionado.
                                </div>
                            @endif

                            @if(isset($note['historical_validation']))
                                <div>
                                    <h4 class="mb-2 font-semibold text-slate-800">Validación con referencia histórica (Bs)</h4>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-sm">
                                            <thead class="bg-slate-100 text-left text-xs uppercase text-slate-600">
                                                <tr>
                                                    <th class="px-3 py-2">Gestión</th>
                                                    <th class="px-3 py-2 text-right">Referencia total</th>
                                                    <th class="px-3 py-2 text-right">Suma del detalle</th>
                                                    <th class="px-3 py-2 text-right">Total en base</th>
                                                    <th class="px-3 py-2 text-right">Diferencia base - referencia</th>
                                                    <th class="px-3 py-2">Resultado</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                @foreach($note['historical_validation'] as $year => $validation)
                                                    <tr>
                                                        <td class="px-3 py-2">{{ $year }}</td>
                                                        <td class="px-3 py-2 text-right">{{ number_format((float) $validation['reference'], 2) }}</td>
                                                        <td class="px-3 py-2 text-right">{{ number_format((float) $validation['account_reference_sum'], 2) }}</td>
                                                        <td class="px-3 py-2 text-right">
                                                            {{ $validation['actual'] === null ? 'No disponible' : number_format((float) $validation['actual'], 2) }}
                                                        </td>
                                                        <td class="px-3 py-2 text-right">
                                                            {{ $validation['difference'] === null ? 'No calculable' : number_format((float) $validation['difference'], 2) }}
                                                        </td>
                                                        <td class="px-3 py-2">{{ $validation['status'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            @if(!empty($note['account_discrepancies']))
                                <details class="rounded-lg border border-slate-200 p-4">
                                    <summary class="cursor-pointer font-semibold text-slate-800">
                                        Diferencias de cuentas frente al detalle histórico ({{ count($note['account_discrepancies']) }})
                                    </summary>
                                    <div class="mt-3 overflow-x-auto">
                                        <table class="min-w-full text-sm">
                                            <thead class="bg-slate-100 text-left text-xs uppercase text-slate-600">
                                                <tr>
                                                    <th class="px-3 py-2">Código</th>
                                                    <th class="px-3 py-2">Detalle histórico</th>
                                                    <th class="px-3 py-2">Gestión</th>
                                                    <th class="px-3 py-2 text-right">Saldo en base</th>
                                                    <th class="px-3 py-2 text-right">Saldo de referencia</th>
                                                    <th class="px-3 py-2 text-right">Diferencia</th>
                                                    <th class="px-3 py-2">Observación</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                @foreach($note['account_discrepancies'] as $discrepancy)
                                                    <tr>
                                                        <td class="px-3 py-2">{{ $discrepancy['code'] }}</td>
                                                        <td class="px-3 py-2">{{ $discrepancy['detail'] }}</td>
                                                        <td class="px-3 py-2">{{ $discrepancy['year'] }}</td>
                                                        <td class="px-3 py-2 text-right">{{ $discrepancy['actual'] === null ? 'No encontrado' : number_format((float) $discrepancy['actual'], 2) }}</td>
                                                        <td class="px-3 py-2 text-right">{{ number_format((float) $discrepancy['reference'], 2) }}</td>
                                                        <td class="px-3 py-2 text-right">{{ $discrepancy['difference'] === null ? 'No calculable' : number_format((float) $discrepancy['difference'], 2) }}</td>
                                                        <td class="px-3 py-2">{{ $discrepancy['status'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </details>
                            @endif

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-gray-900">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Código</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Detalle</th>
                                            @if(isset($note['cutoff_date']))
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Estado del registro</th>
                                            @endif
                                            @foreach($note['years'] as $year)
                                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                                    @if($year === '2026' && isset($note['cutoff_date']))
                                                        2026 al {{ \Illuminate\Support\Carbon::parse($note['cutoff_date'])->format('d/m/Y') }}
                                                    @else
                                                        {{ $year }}{{ in_array($year, $note['estimated_years'] ?? [], true) ? ' (est.)' : '' }}
                                                    @endif
                                                </th>
                                            @endforeach
                                            @if(isset($note['cutoff_date']))
                                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Variación 2024–2023</th>
                                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Variación 2025–2024</th>
                                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Variación 2026</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($note['rows'] as $row)
                                            <tr class="hover:bg-slate-50/70">
                                                <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-700">{{ $row['code'] }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $row['detail'] }}</td>
                                                @if(isset($note['cutoff_date']))
                                                    <td class="px-4 py-3 text-xs text-slate-600">{{ $row['status'] }} · {{ $row['cutoff_status'] }}</td>
                                                @endif
                                                @foreach($note['years'] as $year)
                                                    @php($amount = $row['values'][$year] ?? $row[$year] ?? null)
                                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm {{ $amount !== null && $amount < 0 ? 'text-red-700' : 'text-gray-900' }}">
                                                        @if($amount === null)
                                                            {{ $year === '2026' && isset($note['cutoff_date']) ? $row['cutoff_status'] : 'Sin dato' }}
                                                        @else
                                                            {{ number_format((float) $amount, 2) }}
                                                        @endif
                                                    </td>
                                                @endforeach
                                                @if(isset($note['cutoff_date']))
                                                    @foreach(['2024_2023', '2025_2024', '2026_cutoff'] as $variation)
                                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                                            {{ $row['variations'][$variation] === null ? '—' : number_format((float) $row['variations'][$variation], 2) }}
                                                        </td>
                                                    @endforeach
                                                @endif
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ 2 + count($note['years']) + (isset($note['cutoff_date']) ? 4 : 0) }}" class="px-4 py-6 text-center text-sm text-slate-500">
                                                    No hay saldos de cuenta registrados en la base para este rubro.
                                                </td>
                                            </tr>
                                        @endforelse
                                        @if(!empty($note['rows']))
                                            <tr class="bg-gray-50 font-semibold">
                                                <td class="px-4 py-3 text-sm text-gray-700" colspan="{{ isset($note['cutoff_date']) ? 3 : 2 }}">Total {{ $note['title'] }}</td>
                                                @foreach($note['years'] as $year)
                                                    <td class="px-4 py-3 text-right text-sm text-gray-900">
                                                        {{ $note['totals'][$year] === null ? 'Sin dato' : number_format((float) $note['totals'][$year], 2) }}
                                                    </td>
                                                @endforeach
                                                @if(isset($note['cutoff_date']))
                                                    <td colspan="3" class="px-4 py-3 text-right text-sm text-slate-500">Variación 2026 no calculada: periodo parcial sin corte de origen</td>
                                                @endif
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>

    <style>
        @media print {
            nav, header button, form, .print\:hidden { display: none !important; }
            body { background: white !important; }
            .shadow-sm { box-shadow: none !important; }
            section { break-inside: avoid; }
        }
    </style>
</x-app-layout>
