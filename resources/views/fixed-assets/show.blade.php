<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $asset->name_item }}</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('fixed-assets.index') }}" class="asset-action-button asset-action-button-secondary">{{ __('Volver al listado') }}</a>
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
                        <div><dt>{{ __('Valor de adquisición') }}</dt><dd>${{ number_format($acquisitionValue, 2) }}</dd></div>
                        <div><dt>{{ __('Valor contable') }}</dt><dd>${{ number_format($accountingValue, 2) }}</dd></div>
                        <div><dt>{{ __('Depreciación acumulada') }}</dt><dd>${{ number_format($depreciationAccumulated, 2) }}</dd></div>
                        <div><dt>{{ __('Valor estimado') }}</dt><dd>${{ number_format($estimatedValue, 2) }}</dd></div>
                        <div><dt>{{ __('Porcentaje depreciado') }}</dt><dd>{{ number_format($depreciatedPercentage, 2) }}%</dd></div>
                    </dl>
                </section>

                <section class="asset-detail-card">
                    <h3 class="asset-detail-title">{{ __('Vida útil') }}</h3>
                    <dl class="asset-detail-grid">
                        <div><dt>{{ __('Vida útil total') }}</dt><dd>{{ $usefulLifeYears ?: '-' }}{{ $usefulLifeYears ? ' años' : '' }}</dd></div>
                        <div><dt>{{ __('Antigüedad') }}</dt><dd>{{ $ageYears !== null ? $ageYears . ' años' : '-' }}</dd></div>
                        <div><dt>{{ __('Vida útil restante') }}</dt><dd>{{ $remainingLifeYears !== null ? $remainingLifeYears . ' años' : '-' }}</dd></div>
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
