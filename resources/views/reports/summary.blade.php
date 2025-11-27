@extends('layouts.app')

@section('title', 'Resumen de inventario')

@section('content')
  <div class="text-slate-800 dark:text-slate-800">
    <h1 class="text-2xl font-semibold text-gray-800 mb-6">Resumen de inventario</h1>

    <div class="grid gap-4 sm:grid-cols-2 mb-8">
    <div class="bg-white rounded-lg shadow p-5">
      <p class="text-sm text-gray-500">Productos registrados</p>
      <p class="text-3xl font-semibold text-gray-900">{{ $totalProducts }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-5">
      <p class="text-sm text-gray-500">Stock total</p>
      <p class="text-3xl font-semibold text-gray-900">{{ $totalStock }}</p>
    </div>
  </div>

  <div class="bg-white rounded-lg shadow overflow-hidden">
    <h2 class="text-lg font-semibold text-gray-800 px-4 py-3 border-b">Últimos movimientos</h2>
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-left">
        <tr>
          <th class="px-3 py-2">Fecha</th>
          <th class="px-3 py-2">Producto</th>
          <th class="px-3 py-2">Almacén</th>
          <th class="px-3 py-2 text-center">Tipo</th>
          <th class="px-3 py-2 text-right">Cantidad</th>
        </tr>
      </thead>
      <tbody>
        @forelse($lastMovements as $movement)
          <tr class="border-t">
            <td class="px-3 py-2">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
            <td class="px-3 py-2">{{ $movement->product->name_item }}</td>
            <td class="px-3 py-2">{{ $movement->warehouse->name }}</td>
            <td class="px-3 py-2 text-center">
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $movement->type === 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $movement->type === 'in' ? 'Entrada' : 'Salida' }}
              </span>
            </td>
            <td class="px-3 py-2 text-right font-semibold">{{ $movement->quantity }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-3 py-4 text-center text-gray-500">Sin movimientos registrados.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  </div>
@endsection
