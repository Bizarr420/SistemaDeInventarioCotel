<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Reconocimientos Contables SICAT') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white shadow-sm rounded-lg p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">Reconocimiento acumulado</p>
                    <p class="text-2xl font-bold text-emerald-700">{{ number_format($totalRecognized, 2) }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">Pendientes</p>
                    <p class="text-2xl font-bold text-amber-700">{{ $totalPending }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">Estado del flujo</p>
                    <p class="text-lg font-semibold text-slate-900">Dictamen técnico -> aprobación contable -> registro</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white shadow-sm rounded-lg p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">Deterioros</p>
                    <p class="text-2xl font-bold text-amber-700">{{ $totalDeterioration }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">Bajas / disposiciones</p>
                    <p class="text-2xl font-bold text-red-700">{{ $totalDisposal }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">Cobertura</p>
                    <p class="text-lg font-semibold text-slate-900">Se distingue ajuste por deterioro o baja final</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6 text-gray-900">
                    <div class="mb-4 flex gap-2">
                        <a href="{{ route('accounting-adjustments.export', ['format' => 'pdf'] + request()->query()) }}" class="px-4 py-2 rounded bg-red-600 text-white">Exportar PDF</a>
                        <a href="{{ route('accounting-adjustments.export', ['format' => 'excel'] + request()->query()) }}" class="px-4 py-2 rounded bg-emerald-600 text-white">Exportar Excel</a>
                    </div>

                    <div class="mb-4 flex gap-2">
                        <a href="{{ route('accounting-adjustments.index') }}" class="px-4 py-2 rounded bg-slate-900 text-white">Todos</a>
                        <a href="{{ route('accounting-adjustments.index', ['status' => 'posted']) }}" class="px-4 py-2 rounded bg-emerald-600 text-white">Registrados</a>
                        <a href="{{ route('accounting-adjustments.index', ['status' => 'pending']) }}" class="px-4 py-2 rounded bg-amber-600 text-white">Pendientes</a>
                        <a href="{{ route('accounting-adjustments.index', ['type' => 'deterioration']) }}" class="px-4 py-2 rounded bg-amber-500 text-white">Deterioro</a>
                        <a href="{{ route('accounting-adjustments.index', ['type' => 'disposal']) }}" class="px-4 py-2 rounded bg-red-600 text-white">Baja / Disposición</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Fecha</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Activo</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Dictamen</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tipo</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Técnico</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Contable</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Reconocido</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($adjustments as $adjustment)
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ optional($adjustment->posted_at)->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            <div class="font-medium">{{ $adjustment->product->name_item ?? '-' }}</div>
                                            <div class="text-xs text-gray-500">{{ $adjustment->product->internal_code ?? '' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $adjustment->dictamen?->id ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $adjustment->adjustment_type === 'disposal' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $adjustment->adjustment_type === 'disposal' ? 'Baja / Disposición' : 'Deterioro' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-900">{{ number_format((float) $adjustment->technical_value, 2) }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-900">{{ number_format((float) $adjustment->current_accounting_value, 2) }}</td>
                                        <td class="px-4 py-3 text-right text-sm font-semibold {{ $adjustment->recognized_amount > 0 ? 'text-red-700' : 'text-gray-900' }}">{{ number_format((float) $adjustment->recognized_amount, 2) }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $adjustment->status === 'posted' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $adjustment->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">No hay reconocimientos contables registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $adjustments->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>