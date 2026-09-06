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
      <div class="grid grid-cols-1 gap-4 mb-4">
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-lg font-semibold text-gray-800">Total Activos</h3>
          <p class="text-2xl font-bold text-blue-600">{{ $fixedAssetCount }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-lg font-semibold text-gray-800">Obsoletos (Estado)</h3>
          <p class="text-2xl font-bold text-red-600">{{ $assetObsoleteCount }} <span class="text-base font-medium">({{ number_format($assetObsoleteRate, 2) }}%)</span></p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-lg font-semibold text-gray-800">Operativos</h3>
          <p class="text-2xl font-bold text-green-700">{{ $assetOperationalCount }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-lg font-semibold text-gray-800">Activos con Fallas</h3>
          <p class="text-2xl font-bold text-orange-700">{{ $assetFailureCount }} <span class="text-base font-medium">({{ number_format($assetFailureRate, 2) }}%)</span></p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-lg font-semibold text-gray-800">Deterioro Alto (>=70%)</h3>
          <p class="text-2xl font-bold text-amber-700">{{ $assetHighDeteriorationCount }} <span class="text-base font-medium">({{ number_format($assetHighDeteriorationRate, 2) }}%)</span></p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-lg font-semibold text-gray-800">Deterioro Promedio</h3>
          <p class="text-2xl font-bold text-amber-700">{{ number_format($assetAvgDeterioration, 2) }}%</p>
        </div>
      </div>

      <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('fixed-assets.index') }}" class="bg-green-600 text-white px-4 py-2 rounded">Ver Activos</a>
        <a href="{{ route('fixed-assets.create') }}" class="bg-green-500 text-white px-4 py-2 rounded">Crear Activo</a>
        <a href="{{ route('suppliers.index') }}" class="bg-slate-700 text-white px-4 py-2 rounded">Proveedores</a>
      </div>
    </div>
  </div>

  <div class="mb-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white p-4 rounded-lg shadow">
      <h3 class="text-lg font-semibold text-gray-800 mb-3">Estados de Activos SICAT</h3>
      <div class="grid grid-cols-3 gap-3 text-center">
        <div class="rounded-md bg-gray-50 p-3">
          <p class="text-sm text-gray-600">Con fallas</p>
          <p class="text-xl font-bold text-orange-700">{{ $assetFailureCount }}</p>
        </div>
        <div class="rounded-md bg-gray-50 p-3">
          <p class="text-sm text-gray-600">Deteriorados</p>
          <p class="text-xl font-bold text-amber-700">{{ $assetDeterioratedCount }}</p>
        </div>
        <div class="rounded-md bg-gray-50 p-3">
          <p class="text-sm text-gray-600">Obsoletos</p>
          <p class="text-xl font-bold text-red-700">{{ $assetObsoleteCount }}</p>
        </div>
      </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow">
      <h3 class="text-lg font-semibold text-gray-800 mb-3">Subestado de Obsoletos</h3>
      <div class="grid grid-cols-3 gap-3 text-center">
        <div class="rounded-md bg-gray-50 p-3">
          <p class="text-sm text-gray-600">Pendiente</p>
          <p class="text-xl font-bold text-gray-900">{{ $assetObsoletePendingCount }}</p>
        </div>
        <div class="rounded-md bg-gray-50 p-3">
          <p class="text-sm text-gray-600">Vendido</p>
          <p class="text-xl font-bold text-gray-900">{{ $assetObsoleteSoldCount }}</p>
        </div>
        <div class="rounded-md bg-gray-50 p-3">
          <p class="text-sm text-gray-600">Destruido</p>
          <p class="text-xl font-bold text-gray-900">{{ $assetObsoleteDestroyedCount }}</p>
        </div>
      </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow">
      <h3 class="text-lg font-semibold text-gray-800 mb-3">Análisis Patrimonial</h3>
      <div class="space-y-3">
        <div class="rounded-md bg-gray-50 p-3 text-center">
          <p class="text-sm text-gray-600">Valor Estimado Total</p>
          <p class="text-xl font-bold text-gray-900">{{ number_format($assetTotalEstimatedValue, 2) }}</p>
        </div>
        <div class="rounded-md bg-gray-50 p-3 text-center">
          <p class="text-sm text-gray-600">Valor Técnico vs Contable</p>
          <p class="text-sm text-gray-700">{{ number_format($assetTechnicalTotal, 2) }} / {{ number_format($assetAccountingTotal, 2) }}</p>
          <p class="text-xl font-bold {{ $assetPatrimonialGap >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ number_format($assetPatrimonialGap, 2) }}</p>
        </div>
      </div>
    </div>
  </div>

  <div class="mb-6">
    <a href="{{ route('alerts.index') }}" class="bg-red-500 text-white px-4 py-2 rounded mr-2">Alertas ({{ $unreadAlerts ?? 0 }} no leídas)</a>
    <a href="{{ route('dictamens.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded mr-2">Dictámenes</a>
    <a href="{{ route('accounting-adjustments.index') }}" class="bg-emerald-700 text-white px-4 py-2 rounded mr-2">Reconocimiento Contable</a>
    <a href="{{ route('sicat.overview') }}" class="bg-slate-900 text-white px-4 py-2 rounded mr-2">Resumen SICAT</a>
    <a href="{{ route('reports.deterioration') }}" class="bg-green-500 text-white px-4 py-2 rounded mr-2">Reporte Deterioro</a>
    <a href="{{ route('reports.comparative') }}" class="bg-purple-500 text-white px-4 py-2 rounded">Reporte Comparativo</a>
    <a href="{{ route('reports.financial-notes') }}" class="bg-slate-900 text-white px-4 py-2 rounded mr-2">Notas Financieras</a>
  </div>
@endsection
