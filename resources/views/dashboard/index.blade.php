@extends('layouts.app')

@section('title','Dashboard')
@section('content')
  <h1 class="text-2xl text-gray-900 font-bold mb-4"></h1>

  @if($unreadAlerts > 0)
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
      <strong>¡Atención!</strong> Tienes {{ $unreadAlerts }} alerta(s) de obsolescencia sin leer.
      <a href="{{ route('alerts.index') }}" class="underline">Ver alertas</a>
    </div>
  @endif

  @php
    $assetAlerts = [
      ['key' => 'failures', 'label' => 'Activos con fallas', 'icon' => '🔴', 'color' => 'border-red-200 bg-red-50 text-red-800'],
      ['key' => 'high_deterioration', 'label' => 'Activos con deterioro alto', 'icon' => '🟠', 'color' => 'border-orange-200 bg-orange-50 text-orange-800'],
      ['key' => 'near_end_of_life', 'label' => 'Próximos al final de vida útil', 'icon' => '🟡', 'color' => 'border-yellow-200 bg-yellow-50 text-yellow-800'],
      ['key' => 'pending_verification', 'label' => 'Pendientes de verificación', 'icon' => '🔵', 'color' => 'border-blue-200 bg-blue-50 text-blue-800'],
      ['key' => 'obsolete', 'label' => 'Activos obsoletos', 'icon' => '⚫', 'color' => 'border-gray-300 bg-gray-100 text-gray-800'],
      ['key' => 'verification_differences', 'label' => 'Diferencias en verificación', 'icon' => '🔴', 'color' => 'border-red-200 bg-red-50 text-red-800'],
    ];
  @endphp
  <section class="mb-6">
    <div class="mb-4">
      <h2 class="text-xl text-gray-900 font-bold">Alertas</h2>
      <p class="text-sm text-gray-500">Haz clic en una alerta para abrir el listado filtrado.</p>
    </div>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
      @foreach($assetAlerts as $alert)
        <a href="{{ route('fixed-assets.index', ['alert' => $alert['key']]) }}" class="flex items-center justify-between rounded-lg border p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow {{ $alert['color'] }}">
          <span class="flex items-center gap-2 font-semibold"><span aria-hidden="true">{{ $alert['icon'] }}</span>{{ $alert['label'] }}</span>
          <span class="text-xl font-bold">{{ number_format($assetAlertCounts[$alert['key']] ?? 0) }}</span>
        </a>
      @endforeach
    </div>
  </section>

  <div class="mb-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div>
      <h2 class="text-xl text-gray-900 font-bold mb-4">Gestión de Productos</h2>
      <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Total de ítems</h3>
          <p class="text-2xl font-bold text-blue-600">{{ $totalItems }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Total de inventario</h3>
          <p class="text-2xl font-bold text-slate-700">{{ number_format($stockTotalUnits) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Valor total del inventario</h3>
          <p class="text-2xl font-bold text-emerald-600">{{ number_format($productInventoryValue, 2) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Cantidad de productos</h3>
          <p class="text-2xl font-bold text-indigo-600">{{ $productCount }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Productos con stock</h3>
          <p class="text-2xl font-bold text-green-600">{{ $productsWithStockCount }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Stock disponible</h3>
          <p class="text-2xl font-bold text-green-600">{{ number_format($availableStockUnits) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Equipos pendientes de liquidación</h3>
          <p class="text-2xl font-bold text-amber-600">{{ number_format($pendingLiquidationUnits) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">En uso</h3>
          <p class="text-2xl font-bold text-blue-600">{{ number_format($inUseStockUnits) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Reservado / asignado</h3>
          <p class="text-2xl font-bold text-indigo-600">{{ number_format($reservedStockUnits) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">En reparación</h3>
          <p class="text-2xl font-bold text-orange-600">{{ number_format($repairStockUnits) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Dañado</h3>
          <p class="text-2xl font-bold text-red-600">{{ number_format($damagedStockUnits) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Perdido</h3>
          <p class="text-2xl font-bold text-gray-600">{{ number_format($lostStockUnits) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Dado de baja</h3>
          <p class="text-2xl font-bold text-gray-700">{{ number_format($disposedStockUnits) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Transferencias en tránsito</h3>
          <p class="text-2xl font-bold text-blue-600">{{ number_format($transfersInTransit) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Productos agotados</h3>
          <p class="text-2xl font-bold text-red-600">{{ $outOfStockProductCount }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Bajo stock mínimo</h3>
          <p class="text-2xl font-bold text-amber-600">{{ $lowStockProductCount }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Sin movimiento</h3>
          <p class="text-2xl font-bold text-gray-600">{{ $productsWithoutMovementCount }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Entradas del período</h3>
          <p class="text-2xl font-bold text-cyan-600">{{ number_format($periodEntries) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Salidas del período</h3>
          <p class="text-2xl font-bold text-orange-600">{{ number_format($periodExits) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Devoluciones</h3>
          <p class="text-2xl font-bold text-teal-600">{{ number_format($periodReturns) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Transferencias entre almacenes</h3>
          <p class="text-2xl font-bold text-violet-600">{{ $warehouseTransfers }}</p>
        </div>
      </div>

      <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('inventory.index') }}" class="bg-blue-600 text-white px-4 py-2 rounded">Ver Productos</a>
        <a href="{{ route('inventory.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Crear Producto</a>
        <a href="{{ route('movements.index') }}" class="bg-slate-700 text-white px-4 py-2 rounded">Salidas / Devoluciones</a>
      </div>
    </div>

    <div>
      <h2 class="text-xl text-gray-900 font-bold mb-4">Gestión de Activos</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-4">
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Total de activos</h3>
          <p class="text-2xl font-bold text-blue-600">{{ number_format($fixedAssetCount) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Activos operativos</h3>
          <p class="text-2xl font-bold text-green-700">{{ number_format($assetOperationalCount) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Activos con fallas</h3>
          <p class="text-2xl font-bold text-orange-700">{{ number_format($assetFailureCount) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Activos deteriorados</h3>
          <p class="text-2xl font-bold text-amber-700">{{ number_format($assetDeterioratedCount) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Activos obsoletos</h3>
          <p class="text-2xl font-bold text-red-600">{{ number_format($assetObsoleteCount) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Activos vendidos</h3>
          <p class="text-2xl font-bold text-violet-700">{{ number_format($assetObsoleteSoldCount) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Activos destruidos</h3>
          <p class="text-2xl font-bold text-gray-700">{{ number_format($assetObsoleteDestroyedCount) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Pendientes de verificación</h3>
          <p class="text-2xl font-bold text-amber-600">{{ number_format($assetWithoutVerificationCount) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Activos verificados</h3>
          <p class="text-2xl font-bold text-teal-700">{{ number_format($assetVerifiedCount) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Valor total de adquisición</h3>
          <p class="text-2xl font-bold text-indigo-600">{{ number_format($assetAcquisitionTotal, 2) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Valor contable total</h3>
          <p class="text-2xl font-bold text-slate-700">{{ number_format($assetAccountingTotal, 2) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Valor estimado total</h3>
          <p class="text-2xl font-bold text-cyan-700">{{ number_format($assetTotalEstimatedValue, 2) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Depreciación acumulada</h3>
          <p class="text-2xl font-bold text-orange-700">{{ number_format($assetDepreciationTotal, 2) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Depreciación promedio</h3>
          <p class="text-2xl font-bold text-rose-700">{{ number_format($assetAverageDepreciationPercentage, 2) }}%</p>
        </div>
      </div>

      <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('fixed-assets.index') }}" class="bg-green-600 text-white px-4 py-2 rounded">Ver Activos</a>
        <a href="{{ route('fixed-assets.create') }}" class="bg-green-500 text-white px-4 py-2 rounded">Crear Activo</a>
        <a href="{{ route('suppliers.index') }}" class="bg-slate-700 text-white px-4 py-2 rounded">Proveedores</a>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Valor de adquisición</h3>
          <p class="text-2xl font-bold text-slate-800">{{ number_format($assetAcquisitionTotal, 2) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Depreciación acumulada</h3>
          <p class="text-2xl font-bold text-orange-700">{{ number_format($assetDepreciationTotal, 2) }}</p>
          <p class="text-xs text-gray-500">{{ number_format($assetDepreciationRate, 2) }}% del valor de adquisición</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Verificaciones completas</h3>
          <p class="text-2xl font-bold text-green-700">{{ $assetVerifiedCount }} / {{ $fixedAssetCount }}</p>
          <p class="text-xs text-gray-500">{{ number_format($assetVerificationRate, 2) }}% verificado</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Sin verificación</h3>
          <p class="text-2xl font-bold text-red-700">{{ $assetWithoutVerificationCount }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-semibold text-gray-800">Vida útil por vencer</h3>
          <p class="text-2xl font-bold text-amber-700">{{ $assetExpiringLifeCount }}</p>
          <p class="text-xs text-gray-500">Dentro de los próximos 12 meses</p>
        </div>
      </div>
    </div>
  </div>

  <section class="mb-6">
    <div class="mb-4">
      <h2 class="text-xl text-gray-900 font-bold">Análisis por categoría</h2>
      <p class="text-sm text-gray-500">Haz clic en una categoría para consultar sus activos.</p>
    </div>
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Cantidad de activos por categoría</h3>
        <div class="space-y-3">
          @forelse($assetCategoryAnalysis as $category)
            <a href="{{ route('fixed-assets.index', ['category_id' => $category['id']]) }}" class="group block rounded-md p-2 hover:bg-gray-50">
              <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                <span class="font-medium text-gray-700 group-hover:text-orange-700">{{ $category['name'] }}</span>
                <span class="font-semibold text-gray-900">{{ number_format($category['count']) }}</span>
              </div>
              <div class="h-3 overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full bg-orange-500 transition-all group-hover:bg-orange-600" style="width: {{ ($category['count'] / $assetCategoryMaxCount) * 100 }}%"></div>
              </div>
            </a>
          @empty
            <p class="text-sm text-gray-500">No hay activos categorizados.</p>
          @endforelse
        </div>
      </div>
      <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Valor patrimonial por categoría</h3>
        <div class="space-y-3">
          @forelse($assetCategoryAnalysis->sortByDesc('value') as $category)
            <a href="{{ route('fixed-assets.index', ['category_id' => $category['id']]) }}" class="group block rounded-md p-2 hover:bg-gray-50">
              <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                <span class="font-medium text-gray-700 group-hover:text-indigo-700">{{ $category['name'] }}</span>
                <span class="font-semibold text-gray-900">{{ number_format($category['value'], 2) }}</span>
              </div>
              <div class="h-3 overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full bg-indigo-500 transition-all group-hover:bg-indigo-600" style="width: {{ ($category['value'] / $assetCategoryMaxValue) * 100 }}%"></div>
              </div>
            </a>
          @empty
            <p class="text-sm text-gray-500">No hay valores patrimoniales registrados.</p>
          @endforelse
        </div>
      </div>
    </div>
  </section>

  <div class="mb-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white p-4 rounded-lg shadow">
      <h3 class="text-lg font-semibold text-gray-800 mb-3">Estados de Activos SICAT</h3>
      <div class="grid grid-cols-3 gap-3 text-center">
        <a href="{{ route('fixed-assets.index', ['asset_status' => 'falla']) }}" class="block rounded-md bg-gray-50 p-3 hover:ring-2 hover:ring-orange-300">
          <p class="text-sm text-gray-600">Con fallas</p>
          <p class="text-xl font-bold text-orange-700">{{ $assetFailureCount }}</p>
        </a>
        <a href="{{ route('fixed-assets.index', ['asset_status' => 'deteriorado']) }}" class="block rounded-md bg-gray-50 p-3 hover:ring-2 hover:ring-amber-300">
          <p class="text-sm text-gray-600">Deteriorados</p>
          <p class="text-xl font-bold text-amber-700">{{ $assetDeterioratedCount }}</p>
        </a>
        <a href="{{ route('fixed-assets.index', ['asset_status' => 'obsoleto']) }}" class="block rounded-md bg-gray-50 p-3 hover:ring-2 hover:ring-red-300">
          <p class="text-sm text-gray-600">Obsoletos</p>
          <p class="text-xl font-bold text-red-700">{{ $assetObsoleteCount }}</p>
        </a>
      </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow">
      <h3 class="text-lg font-semibold text-gray-800 mb-3">Subestado de Obsoletos</h3>
      <div class="grid grid-cols-3 gap-3 text-center">
        <a href="{{ route('fixed-assets.index', ['substatus' => 'pendiente']) }}" class="block rounded-md bg-gray-50 p-3 hover:ring-2 hover:ring-gray-300">
          <p class="text-sm text-gray-600">Pendiente</p>
          <p class="text-xl font-bold text-gray-900">{{ $assetObsoletePendingCount }}</p>
        </a>
        <a href="{{ route('fixed-assets.index', ['substatus' => 'vendido']) }}" class="block rounded-md bg-gray-50 p-3 hover:ring-2 hover:ring-violet-300">
          <p class="text-sm text-gray-600">Vendido</p>
          <p class="text-xl font-bold text-gray-900">{{ $assetObsoleteSoldCount }}</p>
        </a>
        <a href="{{ route('fixed-assets.index', ['substatus' => 'destruido']) }}" class="block rounded-md bg-gray-50 p-3 hover:ring-2 hover:ring-gray-400">
          <p class="text-sm text-gray-600">Destruido</p>
          <p class="text-xl font-bold text-gray-900">{{ $assetObsoleteDestroyedCount }}</p>
        </a>
      </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow">
      <h3 class="text-lg font-semibold text-gray-800 mb-3">Análisis Patrimonial</h3>
      <div class="space-y-3">
        <div class="rounded-md bg-gray-50 p-3 text-center">
          <p class="text-sm text-gray-600">Valor estimado total</p>
          <p class="text-xl font-bold text-gray-900">{{ number_format($assetTotalEstimatedValue, 2) }}</p>
        </div>
        <div class="rounded-md bg-gray-50 p-3 text-center">
          <p class="text-sm text-gray-600">Valor técnico vs contable</p>
          <p class="text-sm text-gray-700">{{ number_format($assetTechnicalTotal, 2) }} / {{ number_format($assetAccountingTotal, 2) }}</p>
          <p class="text-xl font-bold {{ $assetPatrimonialGap >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ number_format($assetPatrimonialGap, 2) }}</p>
        </div>
      </div>
    </div>
  </div>

  <section class="mb-6">
    <div class="mb-4">
      <h2 class="text-xl text-gray-900 font-bold">Análisis patrimonial</h2>
      <p class="text-sm text-gray-500">Resumen del valor y nivel de depreciación de los activos.</p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
      <div class="bg-white p-4 rounded-lg shadow"><h3 class="text-sm font-semibold text-gray-800">Valor total de adquisición</h3><p class="text-2xl font-bold text-indigo-600">{{ number_format($assetAcquisitionTotal, 2) }}</p></div>
      <div class="bg-white p-4 rounded-lg shadow"><h3 class="text-sm font-semibold text-gray-800">Depreciación acumulada</h3><p class="text-2xl font-bold text-orange-700">{{ number_format($assetDepreciationTotal, 2) }}</p></div>
      <div class="bg-white p-4 rounded-lg shadow"><h3 class="text-sm font-semibold text-gray-800">Valor neto contable</h3><p class="text-2xl font-bold text-slate-700">{{ number_format($assetAccountingTotal, 2) }}</p></div>
      <div class="bg-white p-4 rounded-lg shadow"><h3 class="text-sm font-semibold text-gray-800">Valor estimado</h3><p class="text-2xl font-bold text-cyan-700">{{ number_format($assetTotalEstimatedValue, 2) }}</p></div>
      <div class="bg-white p-4 rounded-lg shadow"><h3 class="text-sm font-semibold text-gray-800">Diferencia técnico-contable</h3><p class="text-2xl font-bold {{ $assetPatrimonialGap >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ number_format($assetPatrimonialGap, 2) }}</p></div>
      <div class="bg-white p-4 rounded-lg shadow"><h3 class="text-sm font-semibold text-gray-800">Porcentaje depreciado</h3><p class="text-2xl font-bold text-rose-700">{{ number_format($assetDepreciationRate, 2) }}%</p></div>
    </div>
    <div class="bg-white p-4 rounded-lg shadow">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribución del patrimonio por depreciación</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        @php
          $depreciationColors = [
            'Nuevo' => 'bg-green-500',
            'Parcialmente depreciado' => 'bg-blue-500',
            'Altamente depreciado' => 'bg-amber-500',
            'Totalmente depreciado' => 'bg-red-500',
          ];
        @endphp
        @foreach($assetDepreciationBuckets as $label => $count)
          <div class="rounded-md bg-gray-50 p-3">
            <div class="mb-2 flex items-center justify-between gap-2 text-sm">
              <span class="font-medium text-gray-700">{{ $label }}</span>
              <span class="font-bold text-gray-900">{{ number_format($count) }}</span>
            </div>
            <div class="h-4 overflow-hidden rounded-full bg-gray-200">
              <div class="h-full rounded-full {{ $depreciationColors[$label] }}" style="width: {{ ($count / $assetDepreciationBucketMax) * 100 }}%"></div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <div class="mb-6">
    <a href="{{ route('alerts.index') }}" class="bg-red-500 text-white px-4 py-2 rounded mr-2">Alertas ({{ $unreadAlerts ?? 0 }} no leídas)</a>
    <a href="{{ route('dictamens.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded mr-2">Dictámenes</a>
    <a href="{{ route('accounting-adjustments.index') }}" class="bg-emerald-700 text-white px-4 py-2 rounded mr-2">Reconocimiento Contable</a>
    <a href="{{ route('sicat.overview') }}" class="bg-slate-900 text-white px-4 py-2 rounded mr-2">Resumen SICAT</a>
    <a href="{{ route('reports.deterioration') }}" class="bg-green-500 text-white px-4 py-2 rounded mr-2">Reporte Deterioro</a>
    <a href="{{ route('reports.comparative') }}" class="bg-purple-500 text-white px-4 py-2 rounded">Reporte Comparativo</a>
    <a href="{{ route('reports.financial-notes') }}" class="bg-slate-900 text-white px-4 py-2 rounded mr-2">Notas Financieras</a>
  </div>

  <section class="mb-6">
    <div class="mb-4">
      <h2 class="text-xl text-gray-900 font-bold">Análisis por departamento</h2>
      <p class="text-sm text-gray-500">Distribución de activos según Responsable/Departamento.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      @php
        $departmentCharts = [
          ['title' => 'Cantidad de activos', 'key' => 'count', 'max' => $assetDepartmentMaxCount, 'color' => 'bg-blue-500'],
          ['title' => 'Valor patrimonial', 'key' => 'value', 'max' => $assetDepartmentMaxValue, 'color' => 'bg-indigo-500'],
          ['title' => 'Activos con fallas', 'key' => 'failures', 'max' => $assetDepartmentMaxFailures, 'color' => 'bg-orange-500'],
          ['title' => 'Activos obsoletos', 'key' => 'obsolete', 'max' => $assetDepartmentMaxObsolete, 'color' => 'bg-red-500'],
        ];
      @endphp
      @foreach($departmentCharts as $chart)
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ $chart['title'] }} por departamento</h3>
          <div class="space-y-3">
            @forelse($assetDepartmentAnalysis as $department)
              <a href="{{ route('fixed-assets.index', ['assignee' => $department['name'] === 'Sin departamento' ? '__unassigned' : $department['name']]) }}" class="group block">
                <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                  <span class="truncate font-medium text-gray-700" title="{{ $department['name'] }}">{{ $department['name'] }}</span>
                  <span class="font-semibold text-gray-900">
                    @if($chart['key'] === 'value')
                      {{ number_format($department[$chart['key']], 2) }}
                    @else
                      {{ number_format($department[$chart['key']]) }}
                    @endif
                  </span>
                </a>
                <div class="h-3 overflow-hidden rounded-full bg-gray-100">
                  <div class="h-full rounded-full {{ $chart['color'] }}" style="width: {{ ($department[$chart['key']] / $chart['max']) * 100 }}%"></div>
                </div>
              </div>
            @empty
              <p class="text-sm text-gray-500">No hay datos de departamento.</p>
            @endforelse
          </div>
        </div>
      @endforeach
    </div>
  </section>

  <section class="mb-6">
    <div class="mb-4">
      <h2 class="text-xl text-gray-900 font-bold">Análisis por proveedor</h2>
      <p class="text-sm text-gray-500">Distribución de activos y valor adquirido por proveedor.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      @php
        $supplierCharts = [
          ['title' => 'Cantidad de activos', 'key' => 'count', 'max' => $assetSupplierMaxCount, 'color' => 'bg-blue-500'],
          ['title' => 'Valor adquirido', 'key' => 'value', 'max' => $assetSupplierMaxValue, 'color' => 'bg-indigo-500'],
          ['title' => 'Activos con fallas', 'key' => 'failures', 'max' => $assetSupplierMaxFailures, 'color' => 'bg-orange-500'],
          ['title' => 'Activos obsoletos', 'key' => 'obsolete', 'max' => $assetSupplierMaxObsolete, 'color' => 'bg-red-500'],
        ];
      @endphp
      @foreach($supplierCharts as $chart)
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ $chart['title'] }} por proveedor</h3>
          <div class="space-y-3">
            @forelse($assetSupplierAnalysis as $supplier)
              <a href="{{ $supplier['id'] ? route('fixed-assets.index', ['supplier_id' => $supplier['id']]) : route('fixed-assets.index', ['supplier_id' => '__unassigned']) }}" class="group block">
                <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                  <span class="truncate font-medium text-gray-700" title="{{ $supplier['name'] }}">{{ $supplier['name'] }}</span>
                  <span class="font-semibold text-gray-900">
                    @if($chart['key'] === 'value')
                      {{ number_format($supplier[$chart['key']], 2) }}
                    @else
                      {{ number_format($supplier[$chart['key']]) }}
                    @endif
                  </span>
                </a>
                <div class="h-3 overflow-hidden rounded-full bg-gray-100">
                  <div class="h-full rounded-full {{ $chart['color'] }}" style="width: {{ ($supplier[$chart['key']] / $chart['max']) * 100 }}%"></div>
                </div>
              </div>
            @empty
              <p class="text-sm text-gray-500">No hay datos de proveedores.</p>
            @endforelse
          </div>
        </div>
      @endforeach
    </div>
  </section>

  <section class="mb-6">
    <div class="mb-4">
      <h2 class="text-xl text-gray-900 font-bold">Antigüedad y vida útil</h2>
      <p class="text-sm text-gray-500">Antigüedad calculada desde la fecha de adquisición y vida útil configurada.</p>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="bg-white p-4 rounded-lg shadow lg:col-span-2">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Activos por antigüedad</h3>
        <div class="space-y-3">
          @foreach($ageBuckets as $range => $count)
            <div>
              <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                <span class="font-medium text-gray-700">{{ $range }}</span>
                <span class="font-semibold text-gray-900">{{ number_format($count) }}</span>
              </div>
              <div class="h-3 overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full bg-cyan-500" style="width: {{ ($count / $assetAgeMaxCount) * 100 }}%"></div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
      <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Próximos al final de vida útil</h3>
        <p class="mb-4 text-3xl font-bold text-red-700">{{ number_format($assetNearEndOfLife->count()) }}</p>
        <p class="mb-3 text-xs text-gray-500">Menos de 2 años de vida útil restante.</p>
        <div class="space-y-2">
          @forelse($assetNearEndOfLife->take(6) as $life)
            <div class="rounded-md bg-red-50 px-3 py-2">
              <p class="truncate text-sm font-medium text-gray-800">{{ $life['name'] }}</p>
              <p class="text-xs text-red-700">
                {{ number_format($life['remaining_years'], 1) }} años restantes ·
                {{ number_format($life['consumed_percentage'], 1) }}% consumido
              </p>
            </div>
          @empty
            <p class="text-sm text-gray-500">No hay activos próximos al final de su vida útil.</p>
          @endforelse
        </div>
      </div>
    </div>
    <div class="mt-6 bg-white p-4 rounded-lg shadow">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Detalle de vida útil</h3>
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @forelse($assetLifeAnalysis->take(8) as $life)
          <div class="rounded-md bg-gray-50 p-3">
            <p class="truncate text-sm font-semibold text-gray-800" title="{{ $life['name'] }}">{{ $life['name'] }}</p>
            <dl class="mt-2 space-y-1 text-xs text-gray-600">
              <div class="flex justify-between gap-2"><dt>Vida útil total</dt><dd class="font-semibold">{{ $life['total_years'] ?: '-' }}{{ $life['total_years'] ? ' años' : '' }}</dd></div>
              <div class="flex justify-between gap-2"><dt>Consumida</dt><dd class="font-semibold">{{ $life['age_years'] !== null ? number_format($life['age_years'], 1) . ' años' : '-' }}</dd></div>
              <div class="flex justify-between gap-2"><dt>Restante</dt><dd class="font-semibold">{{ $life['remaining_years'] !== null ? number_format($life['remaining_years'], 1) . ' años' : '-' }}</dd></div>
              <div class="flex justify-between gap-2"><dt>Porcentaje consumido</dt><dd class="font-semibold">{{ $life['consumed_percentage'] !== null ? number_format($life['consumed_percentage'], 1) . '%' : '-' }}</dd></div>
            </dl>
          </div>
        @empty
          <p class="text-sm text-gray-500">No hay información de vida útil.</p>
        @endforelse
      </div>
    </div>
  </section>

  <section class="mb-6">
    <div class="mb-4">
      <h2 class="text-xl text-gray-900 font-bold">Estado de Verificación</h2>
      <p class="text-sm text-gray-500">Estado actual según la última verificación registrada para cada activo.</p>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-sm font-semibold text-gray-800">Total verificados</h3>
        <p class="text-2xl font-bold text-green-700">{{ number_format($assetVerificationTotalCount) }}</p>
      </div>
      <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-sm font-semibold text-gray-800">Pendientes</h3>
        <p class="text-2xl font-bold text-amber-600">{{ number_format($assetVerificationPendingCount) }}</p>
      </div>
      <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-sm font-semibold text-gray-800">Con diferencias</h3>
        <p class="text-2xl font-bold text-red-700">{{ number_format($assetVerificationDifferenceCount) }}</p>
      </div>
      <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-sm font-semibold text-gray-800">Última verificación</h3>
        <p class="text-2xl font-bold text-blue-700">
          {{ $lastAssetVerification?->verified_at?->format('d/m/Y') ?? '-' }}
        </p>
      </div>
    </div>
    <div class="bg-white p-4 rounded-lg shadow">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Verificados vs Pendientes vs Con diferencias</h3>
      @php
        $verificationChart = [
          ['label' => 'Verificados', 'count' => $assetVerificationVerifiedCount, 'color' => 'bg-green-500'],
          ['label' => 'Pendientes', 'count' => $assetVerificationPendingCount, 'color' => 'bg-amber-500'],
          ['label' => 'Con diferencias', 'count' => $assetVerificationDifferenceCount, 'color' => 'bg-red-500'],
        ];
        $verificationChartMax = max(1, $fixedAssetCount);
      @endphp
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($verificationChart as $item)
          <div class="rounded-md bg-gray-50 p-3">
            <div class="mb-2 flex items-center justify-between gap-3 text-sm">
              <span class="font-medium text-gray-700">{{ $item['label'] }}</span>
              <span class="font-bold text-gray-900">{{ number_format($item['count']) }}</span>
            </div>
            <div class="h-4 overflow-hidden rounded-full bg-gray-200">
              <div class="h-full rounded-full {{ $item['color'] }}" style="width: {{ ($item['count'] / $verificationChartMax) * 100 }}%"></div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white p-4 rounded-lg shadow">
      <h3 class="text-lg font-semibold text-gray-800 mb-3">Activos por sucursal</h3>
      <div class="space-y-3">
        @forelse($assetBranchAnalysis as $branch)
          <a href="{{ route('fixed-assets.index', ['branch' => $branch['name'] === 'Sin sucursal' ? '__unassigned' : $branch['name']]) }}" class="group block rounded-md p-2 hover:bg-gray-50">
            <div class="mb-1 flex items-center justify-between gap-3 text-sm">
              <span class="font-medium text-gray-700 group-hover:text-orange-700">{{ $branch['name'] }}</span>
              <span class="font-semibold text-gray-900">{{ number_format($branch['count']) }}</span>
            </div>
            <div class="h-3 overflow-hidden rounded-full bg-gray-100">
              <div class="h-full rounded-full bg-orange-500 transition-all group-hover:bg-orange-600" style="width: {{ ($branch['count'] / $assetBranchMaxCount) * 100 }}%"></div>
            </div>
          </a>
        @empty
          <p class="text-sm text-gray-500">No hay datos de sucursal.</p>
        @endforelse
      </div>
    </div>
    <div class="bg-white p-4 rounded-lg shadow">
      <h3 class="text-lg font-semibold text-gray-800 mb-3">Valor patrimonial por sucursal</h3>
      <div class="space-y-3">
        @forelse($assetBranchAnalysis->sortByDesc('value') as $branch)
          <a href="{{ route('fixed-assets.index', ['branch' => $branch['name'] === 'Sin sucursal' ? '__unassigned' : $branch['name']]) }}" class="group block rounded-md p-2 hover:bg-gray-50">
            <div class="mb-1 flex items-center justify-between gap-3 text-sm">
              <span class="font-medium text-gray-700 group-hover:text-indigo-700">{{ $branch['name'] }}</span>
              <span class="font-semibold text-gray-900">{{ number_format($branch['value'], 2) }}</span>
            </div>
            <div class="h-3 overflow-hidden rounded-full bg-gray-100">
              <div class="h-full rounded-full bg-indigo-500 transition-all group-hover:bg-indigo-600" style="width: {{ ($branch['value'] / $assetBranchMaxValue) * 100 }}%"></div>
            </div>
          </a>
        @empty
          <p class="text-sm text-gray-500">No hay valores patrimoniales por sucursal.</p>
        @endforelse
      </div>
    </div>
    <div class="bg-white p-4 rounded-lg shadow">
      <h3 class="text-lg font-semibold text-gray-800 mb-3">Últimas verificaciones</h3>
      <div class="space-y-2">
        @forelse($assetLatestVerifications as $verification)
          <div class="flex items-center justify-between gap-3 rounded-md bg-gray-50 px-3 py-2">
            <span class="truncate text-sm text-gray-700">{{ $verification['asset'] }}</span>
            <span class="whitespace-nowrap text-xs font-semibold text-gray-500">{{ ucfirst($verification['status']) }} · {{ optional($verification['date'])->format('d/m/Y') }}</span>
          </div>
        @empty
          <p class="text-sm text-gray-500">No hay verificaciones registradas.</p>
        @endforelse
      </div>
    </div>
  </div>
@endsection
