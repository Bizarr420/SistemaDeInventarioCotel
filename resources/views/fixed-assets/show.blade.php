<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $asset->name_item }}</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('fixed-assets.index') }}" class="asset-action-button asset-action-button-secondary">{{ __('Volver al listado') }}</a>
                @can('audit.view')
                    <a href="{{ route('audit_logs.entity', ['entityType' => 'FixedAsset', 'entityId' => $asset->id]) }}" class="asset-action-button asset-action-button-secondary">Historial de auditoría</a>
                @endcan
                <a href="{{ route('fixed-assets.edit', $asset) }}" class="asset-action-button asset-action-button-primary">{{ __('Editar') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full max-w-[1400px] mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <p class="text-sm text-gray-500">{{ __('Detalle completo del activo fijo') }}</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900">{{ $asset->name_item }}</h1>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <section class="asset-detail-card">
                    <h3 class="asset-detail-title">{{ __('Información general') }}</h3>
                    <dl class="asset-detail-grid">
                        <div><dt>{{ __('Nombre') }}</dt><dd>{{ $asset->name_item }}</dd></div>
                        <div><dt>{{ __('Categoría') }}</dt><dd>{{ $asset->category->name ?? '-' }}</dd></div>
                        <div><dt>{{ __('Proveedor') }}</dt><dd>{{ $asset->supplier->name ?? '-' }}</dd></div>
                        <div><dt>{{ __('Cantidad') }}</dt><dd>{{ $asset->quantity ?? 0 }}</dd></div>
                    </dl>
                </section>

                <section class="asset-detail-card">
                    <h3 class="asset-detail-title">{{ __('Ubicación') }}</h3>
                    <dl class="asset-detail-grid">
                        <div><dt>{{ __('Sucursal') }}</dt><dd>{{ $asset->location_branch ?? '-' }}</dd></div>
                        <div><dt>{{ __('Piso') }}</dt><dd>{{ $asset->location_floor ?? '-' }}</dd></div>
                        <div><dt>{{ __('Oficina') }}</dt><dd>{{ $asset->location_office ?? '-' }}</dd></div>
                        <div><dt>{{ __('Ubicación específica') }}</dt><dd>{{ $asset->description ?: '-' }}</dd></div>
                    </dl>
                </section>

                <section class="asset-detail-card">
                    <h3 class="asset-detail-title">{{ __('Responsabilidad') }}</h3>
                    <dl class="asset-detail-grid">
                        <div><dt>{{ __('Responsable') }}</dt><dd>{{ $asset->assigned_to ?? '-' }}</dd></div>
                        <div><dt>{{ __('Departamento') }}</dt><dd>{{ $asset->assigned_department ?? '-' }}</dd></div>
                    </dl>
                </section>

                <section class="asset-detail-card">
                    <h3 class="asset-detail-title">{{ __('Información financiera') }}</h3>
                    <dl class="asset-detail-grid">
                        @if($asset->annualValuations->isNotEmpty() || $asset->movements->isNotEmpty())
                            <div><dt>{{ __('Saldo bruto 2026 al corte') }}</dt><dd>Bs {{ number_format((float) $ledgerBalance['gross'], 2) }}</dd></div>
                            <div><dt>{{ __('Depreciación acumulada simulada') }}</dt><dd>Bs {{ number_format($depreciationAccumulated, 2) }}</dd></div>
                            <div><dt>{{ __('Valor neto simulado al corte') }}</dt><dd>Bs {{ number_format($accountingValue, 2) }}</dd></div>
                            <div><dt>{{ __('Corte') }}</dt><dd>{{ now()->format('d/m/Y') }}</dd></div>
                        @else
                            <div><dt>{{ __('Valor de adquisición en ficha') }}</dt><dd>Bs {{ number_format($acquisitionValue, 2) }}</dd></div>
                            <div><dt>{{ __('Valor contable en ficha') }}</dt><dd>Bs {{ number_format($profileAccountingValue, 2) }}</dd></div>
                            <div><dt>{{ __('Valor técnico estimado') }}</dt><dd>Bs {{ number_format($estimatedValue, 2) }}</dd></div>
                        @endif
                    </dl>
                    @if($asset->annualValuations->isNotEmpty() || $asset->movements->isNotEmpty())
                        <p class="mt-3 text-xs text-amber-800">Escenario simulado; los saldos y la depreciación no son contabilidad oficial.</p>
                    @else
                        <p class="mt-3 text-xs text-slate-600">No existe saldo del ledger para este activo; los importes mostrados provienen de la ficha y no son saldos contables.</p>
                    @endif
                </section>

                <section class="asset-detail-card">
                    <h3 class="asset-detail-title">{{ __('Parámetros de depreciación') }}</h3>
                    @if($asset->depreciationParameter)
                        <dl class="asset-detail-grid">
                            <div><dt>Método</dt><dd>{{ $asset->depreciationParameter->method }}</dd></div>
                            <div><dt>Inicio efectivo</dt><dd>{{ $asset->depreciationParameter->depreciation_start_date?->format('d/m/Y') ?? 'No aplica' }}</dd></div>
                            <div><dt>Vida útil</dt><dd>{{ $asset->depreciationParameter->useful_life_years ? $asset->depreciationParameter->useful_life_years.' años' : 'No depreciable' }}</dd></div>
                            <div><dt>Valor residual</dt><dd>Bs {{ number_format((float) $asset->depreciationParameter->residual_value, 2) }}</dd></div>
                            <div><dt>Cuenta de depreciación</dt><dd>{{ $asset->depreciationParameter->depreciationAccount->account_code ?? '-' }}</dd></div>
                            <div><dt>Origen</dt><dd>{{ $asset->depreciationParameter->source }} · {{ $asset->depreciationParameter->profile }}</dd></div>
                        </dl>
                    @else
                        <p class="text-sm text-amber-800">Este activo no tiene parámetros de depreciación asignados.</p>
                    @endif
                </section>

                <section class="asset-detail-card">
                    <h3 class="asset-detail-title">{{ __('Vida útil') }}</h3>
                    <dl class="asset-detail-grid">
                        <div><dt>{{ __('Vida útil total') }}</dt><dd>{{ $usefulLifeYears ?: '-' }}{{ $usefulLifeYears ? ' años' : '' }}</dd></div>
                        <div><dt>{{ __('Antigüedad') }}</dt><dd>{{ $ageYears !== null ? $ageYears . ' años' : '-' }}</dd></div>
                        <div><dt>{{ __('Vida útil restante') }}</dt><dd>{{ $remainingLifeYears !== null ? number_format($remainingLifeYears, 1) . ' años' : '-' }}</dd></div>
                        <div><dt>{{ __('Porcentaje consumido') }}</dt><dd>{{ $consumedPercentage !== null ? number_format($consumedPercentage, 2) . '%' : '-' }}</dd></div>
                    </dl>
                </section>

                <section class="asset-detail-card">
                    <h3 class="asset-detail-title">{{ __('Estado SICAT') }}</h3>
                    <dl class="asset-detail-grid">
                        <div><dt>{{ __('Estado') }}</dt><dd>{{ ucfirst($asset->asset_status ?? 'operativo') }}</dd></div>
                        <div><dt>{{ __('Subestado') }}</dt><dd>{{ ucfirst($asset->obsolete_disposition_status ?? '-') }}</dd></div>
                        <div class="sm:col-span-2"><dt>{{ __('Motivo') }}</dt><dd>{{ $statusReason ?: '-' }}</dd></div>
                    </dl>
                </section>
            </div>

            @if($asset->annualValuations->isNotEmpty())
                <section class="asset-detail-card mt-6">
                    <h3 class="asset-detail-title">{{ __('Valores contables por año (Bs)') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-gray-200 text-xs uppercase text-gray-500">
                                <tr><th class="px-3 py-2">{{ __('Gestión / fecha efectiva') }}</th><th class="px-3 py-2">{{ __('Saldo de escenario (Bs)') }}</th><th class="px-3 py-2">{{ __('Fuente') }}</th></tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($asset->annualValuations as $valuation)
                                    <tr>
                                        <td class="px-3 py-2">{{ $valuation->valuation_year }} · {{ $valuation->as_of_date?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                                        <td class="px-3 py-2">{{ number_format((float) $valuation->amount, 2) }}</td>
                                        <td class="px-3 py-2">{{ $valuation->source ?? 'Origen no registrado' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            @if($period2026Open)
                <section class="asset-detail-card mt-6">
                    <h3 class="asset-detail-title">Registrar movimiento con fecha efectiva 2026</h3>
                    <form method="POST" action="{{ route('fixed-assets.movements.store', $asset) }}" class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        @csrf
                        <label class="text-sm">Tipo
                            <select name="movement_type" required class="mt-1 w-full rounded border-gray-300">
                                <option value="addition">Alta / mejora</option>
                                <option value="disposal">Baja</option>
                                <option value="revaluation">Ajuste de valor</option>
                                <option value="transfer">Traslado</option>
                                <option value="status_change">Cambio de estado</option>
                            </select>
                        </label>
                        <label class="text-sm">Fecha efectiva
                            <input type="date" name="effective_date" required min="2026-01-01" max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}" class="mt-1 w-full rounded border-gray-300">
                        </label>
                        <label class="text-sm">Importe (Bs; positivo en baja)
                            <input type="number" name="amount" step="0.01" min="0.01" class="mt-1 w-full rounded border-gray-300">
                        </label>
                        <label class="text-sm">Disposición de baja
                            <select name="disposition" class="mt-1 w-full rounded border-gray-300">
                                <option value="">No aplica</option><option value="vendido">Vendido</option><option value="destruido">Destruido</option>
                            </select>
                        </label>
                        <label class="text-sm">Estado nuevo
                            <select name="status" class="mt-1 w-full rounded border-gray-300">
                                <option value="">Sin cambio</option><option value="operativo">Operativo</option><option value="falla">Falla</option><option value="deteriorado">Deteriorado</option><option value="obsoleto">Obsoleto</option>
                            </select>
                        </label>
                        <label class="text-sm">Nueva ubicación
                            <input type="text" name="location" maxlength="180" class="mt-1 w-full rounded border-gray-300">
                        </label>
                        <label class="text-sm md:col-span-2">Descripción y respaldo
                            <input type="text" name="description" required maxlength="2000" class="mt-1 w-full rounded border-gray-300">
                        </label>
                        <div class="flex items-end"><button class="rounded bg-emerald-700 px-4 py-2 text-white">Registrar movimiento</button></div>
                    </form>
                    <p class="mt-2 text-xs text-amber-800">La operación solo altera el saldo 2026 desde su fecha efectiva; los cierres 2023–2025 no se modifican.</p>
                </section>
            @endif

            <section class="asset-detail-card mt-6">
                <h3 class="asset-detail-title">Movimientos con fecha efectiva</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-xs uppercase text-gray-500">
                            <tr><th class="px-3 py-2">Fecha</th><th class="px-3 py-2">Tipo</th><th class="px-3 py-2">Importe</th><th class="px-3 py-2">Ubicación / estado</th><th class="px-3 py-2">Origen</th><th class="px-3 py-2">Descripción</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($asset->movements as $movement)
                                <tr>
                                    <td class="px-3 py-2">{{ $movement->effective_date->format('d/m/Y') }}</td>
                                    <td class="px-3 py-2">{{ $movement->movement_type }}</td>
                                    <td class="px-3 py-2">{{ number_format((float) $movement->amount, 2) }}</td>
                                    <td class="px-3 py-2">{{ $movement->location ?? $movement->status ?? '-' }}</td>
                                    <td class="px-3 py-2">{{ $movement->source }}</td>
                                    <td class="px-3 py-2">{{ $movement->description }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Sin movimientos efectivos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @php($latestVerification = $asset->latestVerification)
            <section class="asset-detail-card mt-6">
                <h3 class="asset-detail-title">{{ __('Verificación') }}</h3>
                @if($latestVerification)
                    <dl class="asset-detail-grid">
                        <div><dt>{{ __('Última verificación') }}</dt><dd>{{ optional($latestVerification->verified_at)->format('d/m/Y') }}</dd></div>
                        <div><dt>{{ __('Usuario que verificó') }}</dt><dd>{{ $latestVerification->verifier->name ?? '-' }}</dd></div>
                        <div><dt>{{ __('Resultado') }}</dt><dd>{{ ucfirst($latestVerification->status) }} ({{ $latestVerification->deterioration_level }}% deterioro)</dd></div>
                        <div><dt>{{ __('Observaciones') }}</dt><dd>{{ $latestVerification->notes ?: '-' }}</dd></div>
                    </dl>
                @else
                    <p class="text-sm text-gray-500">{{ __('No hay verificaciones registradas.') }}</p>
                @endif
            </section>

            <section class="asset-detail-card mt-6">
                <h3 class="asset-detail-title">{{ __('Historial de verificaciones') }}</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-xs uppercase text-gray-500">
                            <tr><th class="px-3 py-2">{{ __('Fecha') }}</th><th class="px-3 py-2">{{ __('Usuario') }}</th><th class="px-3 py-2">{{ __('Resultado') }}</th><th class="px-3 py-2">{{ __('Observaciones') }}</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($asset->verifications->sortByDesc('verified_at') as $verification)
                                <tr>
                                    <td class="px-3 py-2">{{ optional($verification->verified_at)->format('d/m/Y') }}</td>
                                    <td class="px-3 py-2">{{ $verification->verifier->name ?? '-' }}</td>
                                    <td class="px-3 py-2">{{ ucfirst($verification->status) }} ({{ $verification->deterioration_level }}%)</td>
                                    <td class="px-3 py-2">{{ $verification->notes ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-3 py-4 text-center text-gray-500">{{ __('No hay historial de verificaciones.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
