@extends('layouts.app')

@section('title', 'Detalle de Producto')

@section('content')
  <div class="max-w-3xl mx-auto bg-white shadow rounded-lg p-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-4">{{ $product->name_item }}</h1>

    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
      <div>
        <dt class="font-semibold text-gray-600">Código interno</dt>
        <dd class="text-gray-900">{{ $product->internal_code ?? '—' }}</dd>
      </div>
      <div>
        <dt class="font-semibold text-gray-600">Nro de parte</dt>
        <dd class="text-gray-900">{{ $product->part_number ?? '—' }}</dd>
      </div>
      <div>
        <dt class="font-semibold text-gray-600">Item</dt>
        <dd class="text-gray-900">{{ $product->item ?? '—' }}</dd>
      </div>
      <div>
        <dt class="font-semibold text-gray-600">Unidad</dt>
        <dd class="text-gray-900">{{ $product->unit ?? '—' }}</dd>
      </div>
      <div>
        <dt class="font-semibold text-gray-600">Categoría</dt>
        <dd class="text-gray-900">{{ $product->category?->name ?? '—' }}</dd>
      </div>
      <div>
        <dt class="font-semibold text-gray-600">Proveedor</dt>
        <dd class="text-gray-900">{{ $product->supplier?->name ?? '—' }}</dd>
      </div>
      <div class="sm:col-span-2">
        <dt class="font-semibold text-gray-600">Descripción</dt>
        <dd class="text-gray-900">{{ $product->description ?? '—' }}</dd>
      </div>
      <div class="sm:col-span-2">
        <dt class="font-semibold text-gray-600">Observación</dt>
        <dd class="text-gray-900">{{ $product->note ?? '—' }}</dd>
      </div>
    </dl>

    <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-3">Stock por almacén</h2>
    <ul class="space-y-2">
      @forelse($product->stocks as $stock)
        <li class="flex justify-between rounded bg-slate-50 px-3 py-2">
          <span>{{ $stock->warehouse->name ?? 'Almacén' }}</span>
          <span class="font-semibold">{{ $stock->current_stock }}</span>
        </li>
      @empty
        <li class="text-gray-500">Sin stock registrado.</li>
      @endforelse
    </ul>

    <div class="mt-6">
      <a href="{{ route('products.index') }}" class="text-orange-600 hover:underline">Volver al listado</a>
    </div>
  </div>
@endsection
