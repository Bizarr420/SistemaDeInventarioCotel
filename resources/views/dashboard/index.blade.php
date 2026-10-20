@extends('layouts.app')

@section('title', 'Dashboard SICAT')

@section('content')
<main class="mx-auto max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-8">
  <header class="flex flex-col justify-between gap-5 md:flex-row md:items-end">
    <div>
      <p class="text-sm font-semibold uppercase tracking-wide text-orange-700">SICAT · Gestión patrimonial</p>
      <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Resumen ejecutivo</h1>
      <p class="mt-2 text-sm text-slate-600">Estado actual de los activos y saldos del período seleccionado.</p>
    </div>
    <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-end gap-3 rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
      <label class="block text-xs font-medium text-slate-600">
        Gestión
        <select name="year" id="dashboard-year" class="mt-1 block rounded-md border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
          @foreach([2023, 2024, 2025, 2026] as $optionYear)
            <option value="{{ $optionYear }}" @selected($year === $optionYear)>{{ $optionYear }}</option>
          @endforeach
        </select>
      </label>
      <label id="dashboard-cutoff-field" class="block text-xs font-medium text-slate-600" style="display: {{ $year === 2026 ? 'block' : 'none' }}">
        Fecha de corte 2026
        <input type="date" name="cutoff" value="{{ $year === 2026 ? $asOf : now()->toDateString() }}" max="{{ now()->toDateString() }}" @disabled($year !== 2026) class="mt-1 block rounded-md border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
      </label>
      <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700" type="submit">Aplicar</button>
    </form>
  </header>

  <section aria-labelledby="executive-title">
    <div class="mb-3 flex items-center justify-between gap-3">
      <h2 id="executive-title" class="text-lg font-semibold text-slate-900">Activos fijos</h2>
      <span class="text-xs text-slate-500">Saldos a {{ \Illuminate\Support\Carbon::parse($asOf)->format('d/m/Y') }}</span>
    </div>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">
      <a href="{{ route('fixed-assets.index') }}" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:border-slate-400">
        <p class="text-xs font-medium text-slate-500">Total de activos</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($fixedAssetCount) }}</p>
        <p class="mt-1 text-xs text-slate-500">Inventario patrimonial actual</p>
      </a>
      <a href="{{ route('fixed-assets.index', ['asset_status' => 'operativo']) }}" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:border-emerald-400">
        <p class="text-xs font-medium text-slate-500">Activos operativos</p>
        <p class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($assetOperationalCount) }}</p>
        <p class="mt-1 text-xs text-slate-500">Estado registrado actual</p>
      </a>
      <a href="{{ route('fixed-assets.index', ['alert' => 'failures']) }}" class="rounded-lg border border-red-200 bg-red-50 p-4 shadow-sm transition hover:border-red-400">
        <p class="text-xs font-medium text-red-800">Activos que requieren atención</p>
        <p class="mt-2 text-2xl font-bold text-red-800">{{ number_format($assetAttentionCount) }}</p>
        <p class="mt-1 text-xs text-red-700">Fallas, deterioro u obsolescencia</p>
      </a>
      <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-medium text-slate-500">Valor bruto · escenario</p>
        <p class="mt-2 text-xl font-bold text-slate-900">{{ $assetAcquisitionDisplay }}</p>
        <p class="mt-1 text-xs text-slate-500">Gestión {{ $year }}</p>
      </div>
      <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-medium text-slate-500">Depreciación acumulada simulada</p>
        <p class="mt-2 text-xl font-bold text-slate-900">{{ $assetDepreciationDisplay }}</p>
        <p class="mt-1 text-xs text-slate-500">Gestión {{ $year }}</p>
      </div>
      <div class="rounded-lg border border-slate-200 bg-slate-900 p-4 shadow-sm">
        <p class="text-xs font-medium text-slate-300">Valor neto del escenario</p>
        <p class="mt-2 text-xl font-bold text-white">{{ $assetAccountingDisplay }}</p>
        <p class="mt-1 text-xs text-slate-300">Bruto menos depreciación</p>
      </div>
    </div>
  </section>

  <section aria-labelledby="depreciation-title" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
    <div class="mb-4 flex items-center justify-between gap-3">
      <div>
        <h2 id="depreciation-title" class="text-lg font-semibold text-slate-900">Depreciación de activos fijos</h2>
        <p class="mt-1 text-sm text-slate-500">Escenario SICAT simulado. Los valores no constituyen saldos contables oficiales. No son contabilidad oficial auditada.</p>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-6">
      <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
        <p class="text-xs font-medium text-slate-500">Depreciación acumulada</p>
        <p class="mt-2 text-lg font-bold text-slate-900">Bs {{ number_format((float) ($depreciationMetrics['accumulated'] ?? 0), 2, ',', '.') }}</p>
      </div>
      <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
        <p class="text-xs font-medium text-slate-500">Depreciación del período</p>
        <p class="mt-2 text-lg font-bold text-slate-900">Bs {{ $depreciationMetrics['period'] ?? '0,00' }}</p>
      </div>
      <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
        <p class="text-xs font-medium text-slate-500">Valor bruto</p>
        <p class="mt-2 text-lg font-bold text-slate-900">Bs {{ number_format((float) ($depreciationMetrics['gross'] ?? 0), 2, ',', '.') }}</p>
      </div>
      <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
        <p class="text-xs font-medium text-slate-500">Valor neto en libros</p>
        <p class="mt-2 text-lg font-bold text-slate-900">Bs {{ number_format((float) ($depreciationMetrics['net'] ?? 0), 2, ',', '.') }}</p>
      </div>
      <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
        <p class="text-xs font-medium text-slate-500">% depreciado</p>
        <p class="mt-2 text-lg font-bold text-slate-900">{{ $depreciationMetrics['percentage'] ?? '0,00' }}%</p>
      </div>
      <div class="rounded-lg border border-slate-200 bg-slate-900 p-3">
        <p class="text-xs font-medium text-slate-300">Valor pendiente de depreciar</p>
        <p class="mt-2 text-lg font-bold text-white">Bs {{ number_format((float) ($depreciationMetrics['pending'] ?? 0), 2, ',', '.') }}</p>
      </div>
    </div>

    <div class="mt-5 grid gap-4 lg:grid-cols-[1.05fr_1.45fr]">
      <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
        <h3 class="text-sm font-semibold text-slate-900">Estado de depreciación</h3>
        <div class="mt-4 space-y-3">
          @foreach($depreciationStatusBuckets as $label => $count)
            @php
              $depreciationLink = match ($label) {
                  'Sin parámetros' => route('fixed-assets.index', ['alert' => 'missing_depreciation_parameters']),
                  'Nuevo' => route('fixed-assets.index'),
                  'Parcialmente depreciado' => route('fixed-assets.index', ['alert' => 'missing_depreciation_parameters']),
                  'Altamente depreciado' => route('fixed-assets.index'),
                  'Totalmente depreciado' => route('fixed-assets.index'),
                  default => route('fixed-assets.index')
              };
              $maxBucket = max(1, collect($depreciationStatusBuckets)->max());
            @endphp
            <a href="{{ $depreciationLink }}" class="block rounded-md border border-slate-200 bg-white p-2.5 hover:border-slate-300">
              <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                <span class="font-medium text-slate-700">{{ $label }}</span>
                <span class="font-semibold text-slate-900">{{ number_format($count) }}</span>
              </div>
              <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-amber-500" style="width: {{ ($count / $maxBucket) * 100 }}%"></div>
              </div>
            </a>
          @endforeach
        </div>
      </div>

      <div class="rounded-lg border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between gap-3">
          <h3 class="text-sm font-semibold text-slate-900">Evolución de la depreciación</h3>
          <div class="flex flex-wrap gap-2" role="tablist" aria-label="Cambiar intervalo de depreciación">
            <button type="button" data-depreciation-tab="monthly" class="rounded-md bg-slate-900 px-2.5 py-1 text-[11px] font-semibold text-white">Mensual</button>
            <button type="button" data-depreciation-tab="yearly" class="rounded-md bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-700">Anual</button>
          </div>
        </div>

        @foreach(['monthly', 'yearly'] as $mode)
          <div id="depreciation-panel-{{ $mode }}" class="mt-4 space-y-3" @if($mode !== 'monthly') hidden @endif>
            @if(empty($depreciationEvolution[$mode]))
              <p class="rounded-md bg-slate-50 p-3 text-sm text-slate-600">No existe información histórica suficiente para mostrar la evolución de {{ $mode === 'monthly' ? 'meses' : 'gestiones' }} en este período.</p>
            @else
              @php($chartMax = max(1, collect($depreciationEvolution[$mode])->map(fn ($row) => max((float) ($row['period'] ?? 0), (float) ($row['accumulated'] ?? 0), (float) ($row['net'] ?? 0)))->max()))
              @foreach($depreciationEvolution[$mode] as $point)
                <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5">
                  <div class="mb-2 flex items-center justify-between gap-2 text-[11px] text-slate-600">
                    <span class="font-medium text-slate-700">{{ $point['label'] }}</span>
                    <span>Dep. período: Bs {{ number_format((float) ($point['period'] ?? 0), 2, ',', '.') }}</span>
                  </div>
                  <div class="space-y-2">
                    <div>
                      <div class="mb-1 flex justify-between text-[10px] text-slate-500"><span>Período</span><span>Bs {{ number_format((float) ($point['period'] ?? 0), 2, ',', '.') }}</span></div>
                      <div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-amber-500" style="width: {{ (($point['period'] ?? 0) / $chartMax) * 100 }}%"></div></div>
                    </div>
                    <div>
                      <div class="mb-1 flex justify-between text-[10px] text-slate-500"><span>Acumulada</span><span>Bs {{ number_format((float) ($point['accumulated'] ?? 0), 2, ',', '.') }}</span></div>
                      <div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-slate-900" style="width: {{ (($point['accumulated'] ?? 0) / $chartMax) * 100 }}%"></div></div>
                    </div>
                    <div>
                      <div class="mb-1 flex justify-between text-[10px] text-slate-500"><span>Valor neto</span><span>Bs {{ number_format((float) ($point['net'] ?? 0), 2, ',', '.') }}</span></div>
                      <div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-emerald-500" style="width: {{ (($point['net'] ?? 0) / $chartMax) * 100 }}%"></div></div>
                    </div>
                  </div>
                </div>
              @endforeach
            @endif
          </div>
        @endforeach
      </div>
    </div>

    <div class="mt-5 rounded-lg border border-slate-200 bg-slate-50 p-4">
      <div class="flex items-center justify-between gap-3">
        <h3 class="text-sm font-semibold text-slate-900">Análisis por categoría y cuenta</h3>
        <span class="text-[11px] text-slate-500">Top {{ count($depreciationBreakdown) }} categorías</span>
      </div>
      <div class="mt-3 grid gap-2 md:grid-cols-2 xl:grid-cols-3">
        @forelse($depreciationBreakdown as $row)
          <div class="rounded-md border border-slate-200 bg-white p-3">
            <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">{{ Str::limit($row['label'], 32) }}</p>
            <p class="mt-1 text-base font-bold text-slate-900">{{ $row['display'] }}</p>
          </div>
        @empty
          <p class="text-sm text-slate-600">No hay registros de depreciación para el período seleccionado.</p>
        @endforelse
      </div>
    </div>
  </section>

  <section aria-labelledby="inventory-title" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h2 id="inventory-title" class="text-base font-semibold text-slate-900">Inventario de productos</h2>
        <p class="mt-1 text-xs text-slate-500">Resumen independiente; no se suma al patrimonio de activos fijos.</p>
      </div>
      <a href="{{ route('inventory.index') }}" class="text-sm font-semibold text-orange-700 hover:text-orange-900">Abrir inventario <span aria-hidden="true">→</span></a>
    </div>
    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
      <div class="rounded-md bg-slate-50 px-4 py-3">
        <p class="text-xs text-slate-500">Productos de servicio</p>
        <p class="mt-1 text-lg font-semibold text-slate-900">{{ number_format($productCount) }}</p>
      </div>
      <div class="rounded-md bg-slate-50 px-4 py-3">
        <p class="text-xs text-slate-500">Unidades en existencia</p>
        <p class="mt-1 text-lg font-semibold text-slate-900">{{ number_format((int) ($inventorySummary->units ?? 0)) }}</p>
      </div>
      <div class="rounded-md bg-slate-50 px-4 py-3">
        <p class="text-xs text-slate-500">Valor de inventario de productos</p>
        <p class="mt-1 text-lg font-semibold text-slate-900">Bs {{ $inventoryValueDisplay }}</p>
      </div>
    </div>
  </section>

  <section aria-labelledby="alerts-title">
    <div class="mb-3 flex items-end justify-between gap-3">
      <div>
        <h2 id="alerts-title" class="text-lg font-semibold text-slate-900">Alertas y tareas pendientes</h2>
        <p class="mt-1 text-sm text-slate-500">Prioridades agrupadas; cada cantidad abre el listado correspondiente.</p>
      </div>
      <a href="{{ route('alerts.index') }}" class="shrink-0 text-sm font-semibold text-orange-700 hover:text-orange-900">Centro de alertas</a>
    </div>
    <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
      <article class="rounded-lg border border-red-200 bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
          <h3 class="font-semibold text-slate-900">Estado de los activos</h3>
          <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-800">Prioridad alta</span>
        </div>
        <div class="grid grid-cols-3 gap-2">
          @foreach([
            ['label' => 'Fallas', 'count' => $assetFailureCount, 'filter' => ['alert' => 'failures'], 'tone' => 'text-red-700'],
            ['label' => 'Deteriorados', 'count' => $assetDeterioratedCount, 'filter' => ['asset_status' => 'deteriorado'], 'tone' => 'text-amber-700'],
            ['label' => 'Obsoletos', 'count' => $assetObsoleteCount, 'filter' => ['alert' => 'obsolete'], 'tone' => 'text-slate-700'],
          ] as $alert)
            <a href="{{ route('fixed-assets.index', $alert['filter']) }}" class="rounded-md bg-slate-50 p-3 hover:bg-slate-100">
              <span class="block text-xs text-slate-500">{{ $alert['label'] }}</span>
              <span class="mt-1 block text-xl font-bold {{ $alert['tone'] }}">{{ number_format($alert['count']) }}</span>
            </a>
          @endforeach
        </div>
      </article>
      <article class="rounded-lg border border-amber-200 bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
          <h3 class="font-semibold text-slate-900">Verificaciones</h3>
          <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Requiere seguimiento</span>
        </div>
        <div class="grid grid-cols-2 gap-2">
          <a href="{{ route('fixed-assets.index', ['alert' => 'pending_verification']) }}" class="rounded-md bg-slate-50 p-3 hover:bg-slate-100">
            <span class="block text-xs text-slate-500">Sin verificación registrada</span>
            <span class="mt-1 block text-xl font-bold text-amber-700">{{ number_format($verificationPendingCount) }}</span>
          </a>
          <a href="{{ route('fixed-assets.index', ['alert' => 'verification_differences']) }}" class="rounded-md bg-slate-50 p-3 hover:bg-slate-100">
            <span class="block text-xs text-slate-500">Con diferencias en la última</span>
            <span class="mt-1 block text-xl font-bold text-red-700">{{ number_format($verificationDifferenceCount) }}</span>
          </a>
        </div>
        <p class="mt-2 text-xs text-slate-500">{{ number_format($verificationRecordedCount) }} con verificación registrada; {{ number_format($verificationClearCount) }} sin diferencias según la más reciente.</p>
      </article>
    </div>

    <details class="mt-3 rounded-lg border border-slate-200 bg-white shadow-sm">
      <summary class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-slate-700">
        Consultar alertas adicionales: vida útil, datos incompletos y operaciones pendientes
      </summary>
      <div class="grid grid-cols-1 gap-3 border-t border-slate-100 p-4 md:grid-cols-4">
        <a href="{{ route('fixed-assets.index', ['alert' => 'near_end_of_life', 'as_of' => $asOf]) }}" class="rounded-md bg-slate-50 p-3 hover:bg-slate-100">
          <span class="block text-xs font-medium text-slate-500">Vida útil restante ≤ 2 años (incluye vencidos)</span>
          <span class="mt-1 block text-xl font-bold text-amber-700">{{ number_format($nearEndOfLifeCount) }}</span>
        </a>
        <a href="{{ route('fixed-assets.index', ['alert' => 'incomplete_data']) }}" class="rounded-md bg-slate-50 p-3 hover:bg-slate-100">
          <span class="block text-xs font-medium text-slate-500">Datos de ficha incompletos</span>
          <span class="mt-1 block text-xl font-bold text-slate-800">{{ number_format($missingRequiredDataCount) }}</span>
          <span class="mt-1 block text-xs text-slate-500">Sin proveedor: {{ number_format((int) $profileDataGaps->missing_supplier) }} · sin responsable: {{ number_format((int) $profileDataGaps->missing_assignee) }}</span>
          <span class="block text-xs text-slate-500">Sin sucursal: {{ number_format((int) $profileDataGaps->missing_branch) }} · sin adquisición: {{ number_format((int) $profileDataGaps->missing_acquisition_date) }}</span>
        </a>
        <a href="{{ route('fixed-assets.index', ['alert' => 'missing_depreciation_parameters']) }}" class="rounded-md bg-slate-50 p-3 hover:bg-slate-100">
          <span class="block text-xs font-medium text-slate-500">Sin parámetros de depreciación</span>
          <span class="mt-1 block text-xl font-bold text-slate-800">{{ number_format($assetsWithoutDepreciationParameters) }}</span>
        </a>
        <div class="rounded-md bg-slate-50 p-3">
          <span class="block text-xs font-medium text-slate-500">Aprobaciones pendientes</span>
          <div class="mt-2 flex flex-wrap gap-3 text-sm">
            <a href="{{ route('dictamens.index') }}" class="font-semibold text-slate-800 hover:text-orange-700">Dictámenes: {{ number_format($pendingDictamens) }}</a>
            <a href="{{ route('accounting-adjustments.index') }}" class="font-semibold text-slate-800 hover:text-orange-700">Ajustes: {{ number_format($pendingAdjustments) }}</a>
          </div>
        </div>
      </div>
    </details>
  </section>

  <section aria-labelledby="analysis-title">
    <div class="mb-3 flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
      <div>
        <h2 id="analysis-title" class="text-lg font-semibold text-slate-900">Análisis resumido</h2>
        <p class="mt-1 text-sm text-slate-500">Seleccione un corte para explorar los activos filtrados.</p>
      </div>
      <a href="{{ route('reports.summary') }}" class="text-sm font-semibold text-orange-700 hover:text-orange-900">Ver informes detallados <span aria-hidden="true">→</span></a>
    </div>
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
      <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <h3 class="font-semibold text-slate-900">Distribución por estado actual</h3>
        <p class="mb-4 mt-1 text-xs text-slate-500">Estados guardados en las fichas de activos; seleccione una fila para filtrar.</p>
        <div class="space-y-3">
          @php($stateMax = max(1, collect($stateChart)->max('value')))
          @foreach($stateChart as $row)
            <a href="{{ $row['url'] }}" class="group block">
              <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                <span class="font-medium text-slate-700 group-hover:text-orange-800">{{ $row['label'] }}</span>
                <span class="font-semibold text-slate-900">{{ $row['display'] }}</span>
              </div>
              <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full {{ match($row['tone']) { 'green' => 'bg-emerald-500', 'red' => 'bg-red-500', 'amber' => 'bg-amber-500', default => 'bg-slate-500' } }}" style="width: {{ ($row['value'] / $stateMax) * 100 }}%"></div>
              </div>
            </a>
          @endforeach
        </div>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
          <div>
            <h3 class="font-semibold text-slate-900">Distribución de activos</h3>
            <p id="analysis-chart-label" class="mt-1 text-xs text-slate-500">Categoría · valor bruto de la gestión {{ $year }}</p>
          </div>
          <div class="flex flex-wrap gap-1" role="tablist" aria-label="Cambiar análisis">
            @foreach($analysisCharts as $key => $chart)
              <button type="button" role="tab" aria-selected="{{ $key === 'category' ? 'true' : 'false' }}" aria-controls="analysis-panel-{{ $key }}" data-analysis-tab="{{ $key }}" class="rounded-md px-2 py-1 text-xs font-semibold {{ $key === 'category' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                {{ ucfirst($key === 'age' ? 'Antigüedad' : ($key === 'department' ? 'Departamento' : ($key === 'branch' ? 'Sucursal' : 'Categoría'))) }}
              </button>
            @endforeach
          </div>
        </div>
        @foreach($analysisCharts as $key => $chart)
          <div id="analysis-panel-{{ $key }}" class="mt-4 space-y-3" role="tabpanel" data-analysis-panel="{{ $key }}" @if($key !== 'category') hidden @endif>
            @php($chartMax = max(1, collect($chart['rows'])->max('value')))
            @forelse($chart['rows'] as $row)
              <a href="{{ $row['url'] }}" class="group block" title="Abrir activos: {{ $row['label'] }}">
                <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                  <span class="truncate font-medium text-slate-700 group-hover:text-orange-800">{{ $row['label'] }}</span>
                  <span class="shrink-0 font-semibold text-slate-900">{{ $row['display'] }}</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                  <div class="h-full rounded-full bg-blue-600" style="width: {{ ($row['value'] / $chartMax) * 100 }}%"></div>
                </div>
              </a>
            @empty
              <p class="text-sm text-slate-500">No hay registros para este análisis.</p>
            @endforelse
            @if($key === 'category' && count($chart['rows']) >= 6)
              <a href="{{ route('reports.financial-notes', $dashboardFilter) }}" class="inline-block pt-1 text-xs font-semibold text-orange-700 hover:text-orange-900">Consultar todas las cuentas en Notas Financieras</a>
            @endif
          </div>
        @endforeach
      </article>
    </div>
  </section>

  <section aria-labelledby="activity-title" class="grid grid-cols-1 gap-4 lg:grid-cols-3">
    <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm lg:col-span-2">
      <div class="mb-3 flex items-center justify-between gap-3">
        <div>
          <h2 id="activity-title" class="font-semibold text-slate-900">Actividad reciente</h2>
          <p class="mt-1 text-xs text-slate-500">Operaciones registradas en el sistema; no se generan eventos de muestra.</p>
        </div>
        <a href="{{ route('fixed-assets.index') }}" class="text-xs font-semibold text-orange-700 hover:text-orange-900">Ver activos</a>
      </div>
      @if($activity === [])
        <p class="rounded-md bg-slate-50 px-3 py-4 text-sm text-slate-500">Todavía no hay actividad registrada.</p>
      @else
        <ol class="divide-y divide-slate-100">
          @foreach($activity as $item)
            <li class="flex flex-col justify-between gap-1 py-3 sm:flex-row sm:items-center">
              <div class="min-w-0">
                <p class="truncate text-sm font-medium text-slate-800">{{ $item['type'] }} · {{ $item['name'] }}</p>
                @if($item['description'])
                  <p class="truncate text-xs text-slate-500">{{ $item['description'] }}</p>
                @endif
              </div>
              <div class="flex shrink-0 items-center gap-2 text-xs text-slate-500">
                @if($item['simulated'])  @endif
                {{ \Illuminate\Support\Carbon::parse($item['date'])->format('d/m/Y') }}
              </div>
            </li>
          @endforeach
        </ol>
      @endif
    </article>
    <aside class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
      <h2 class="font-semibold text-slate-900">Accesos rápidos</h2>
      <div class="mt-3 grid gap-2">
        @if($userCanManageAssets)
          <a href="{{ route('fixed-assets.acquisitions.create') }}" class="rounded-md bg-slate-900 px-3 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">Crear activo / registrar alta 2026</a>
        @else
          <a href="{{ route('fixed-assets.index') }}" class="rounded-md bg-slate-900 px-3 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">Consultar activos</a>
        @endif
        <a href="{{ route('fixed-assets.index') }}" class="rounded-md border border-slate-200 px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Registrar movimiento</a>
        <a href="{{ route('fixed-assets.verifications.create') }}" class="rounded-md border border-slate-200 px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Realizar verificación</a>
        <a href="{{ route('reports.financial-notes', $dashboardFilter) }}" class="rounded-md border border-slate-200 px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Consultar notas financieras</a>
        @if($userCanManageAssets)
          <a href="{{ route('accounting-adjustments.index') }}" class="rounded-md border border-slate-200 px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Ajustes patrimoniales</a>
        @endif
      </div>
      <p class="mt-4 text-xs text-slate-500">Última verificación registrada: {{ $latestVerification ? \Illuminate\Support\Carbon::parse($latestVerification)->format('d/m/Y') : 'ninguna' }}.</p>
    </aside>
  </section>
</main>

<script>
  (() => {
    const yearSelect = document.getElementById('dashboard-year');
    const cutoffField = document.getElementById('dashboard-cutoff-field');
    const tabs = document.querySelectorAll('[data-analysis-tab]');
    const panels = document.querySelectorAll('[data-analysis-panel]');
    const chartLabel = document.getElementById('analysis-chart-label');
    const labels = @json(collect($analysisCharts)->mapWithKeys(fn ($chart, $key) => [$key => $chart['label']])->all());

    const updateCutoffField = () => {
      const is2026 = yearSelect.value === '2026';
      cutoffField.style.display = is2026 ? 'block' : 'none';
      cutoffField.querySelector('input').disabled = !is2026;
    };
    yearSelect?.addEventListener('change', updateCutoffField);
    if (yearSelect && cutoffField) {
      updateCutoffField();
    }

    tabs.forEach((tab) => tab.addEventListener('click', () => {
      const selected = tab.dataset.analysisTab;
      tabs.forEach((item) => {
        const active = item === tab;
        item.setAttribute('aria-selected', active ? 'true' : 'false');
        item.classList.toggle('bg-slate-900', active);
        item.classList.toggle('text-white', active);
        item.classList.toggle('bg-slate-100', !active);
        item.classList.toggle('text-slate-600', !active);
      });
      panels.forEach((panel) => {
        panel.hidden = panel.dataset.analysisPanel !== selected;
      });
      chartLabel.textContent = labels[selected] + (selected === 'category' ? ' · gestión {{ $year }}' : '');
    }));

    const depTabs = document.querySelectorAll('[data-depreciation-tab]');
    const depPanels = document.querySelectorAll('[id^="depreciation-panel-"]');
    depTabs.forEach((tab) => tab.addEventListener('click', () => {
      const selected = tab.dataset.depreciationTab;
      depTabs.forEach((item) => {
        const active = item === tab;
        item.classList.toggle('bg-slate-900', active);
        item.classList.toggle('text-white', active);
        item.classList.toggle('bg-slate-100', !active);
        item.classList.toggle('text-slate-700', !active);
      });
      depPanels.forEach((panel) => {
        panel.hidden = panel.id !== 'depreciation-panel-' + selected;
      });
    }));
  })();
</script>
@endsection
