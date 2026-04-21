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
      <div class="grid grid-cols-1 gap-4 mb-4">
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-lg font-semibold text-gray-800">Total Productos</h3>
          <p class="text-2xl font-bold text-blue-600">{{ $productCount }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-lg font-semibold text-gray-800">Productos Obsoletos</h3>
          <p class="text-2xl font-bold text-red-600">{{ $obsoleteProductCount }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-lg font-semibold text-gray-800">Alertas Totales</h3>
          <p class="text-2xl font-bold text-red-600">{{ $totalAlerts ?? 0 }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-lg font-semibold text-gray-800">Alertas No Leídas</h3>
          <p class="text-2xl font-bold text-orange-600">{{ $unreadAlerts }}</p>
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
    <a href="{{ route('reports.deterioration') }}" class="bg-green-500 text-white px-4 py-2 rounded mr-2">Reporte Deterioro</a>
    <a href="{{ route('reports.comparative') }}" class="bg-purple-500 text-white px-4 py-2 rounded">Reporte Comparativo</a>
  </div>
@endsection
