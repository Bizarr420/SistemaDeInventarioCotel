@extends('layouts.app')

@section('title', 'Productos')

@section('content')
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold text-gray-800">Productos</h1>
    <a href="{{ route('products.create') }}" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">Nuevo producto</a>
  </div>

  <form method="get" action="{{ route('products.index') }}" class="mb-4 flex flex-col sm:flex-row gap-3">
    <div class="flex-1">
      <input type="search" name="q" value="{{ $search }}" placeholder="Buscar por código, nombre o MAC"
             class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-orange-500 focus:ring-orange-500">
    </div>
    <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-700">Buscar</button>
  </form>

  <div class="overflow-x-auto bg-white shadow rounded-lg">
    <table class="min-w-full text-sm">
      <thead>
        <tr class="bg-slate-50 text-left">
          <th class="px-3 py-2">COD.INT.</th>
          <th class="px-3 py-2">Nro de parte</th>
          <th class="px-3 py-2">Item</th>
          <th class="px-3 py-2">Nombre de Ítem</th>
          <th class="px-3 py-2">Und</th>
          <th class="px-3 py-2 text-center">Stock total</th>
          <th class="px-3 py-2">Por almacén</th>
          <th class="px-3 py-2"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($products as $product)
          <tr class="border-t text-gray-700">
            <td class="px-3 py-2">{{ $product->internal_code }}</td>
            <td class="px-3 py-2">{{ $product->part_number }}</td>
            <td class="px-3 py-2">{{ $product->item }}</td>
            <td class="px-3 py-2 font-medium text-gray-800">{{ $product->name_item }}</td>
            <td class="px-3 py-2">{{ $product->unit }}</td>
            <td class="px-3 py-2 text-center font-semibold text-gray-900">{{ $product->total_stock }}</td>
            <td class="px-3 py-2">
              @forelse($product->stocks as $stock)
                <span class="inline-flex items-center gap-1 rounded border border-slate-200 bg-slate-50 px-2 py-0.5 mr-1 text-slate-700">
                  <span class="font-medium text-slate-900">{{ $stock->warehouse->code ?? $stock->warehouse->name }}</span>
                  <span>- {{ $stock->current_stock }}</span>
                  @if($stock->location)
                    <span class="text-xs text-slate-500">({{ $stock->location }})</span>
                  @endif
                </span>
              @empty
                <span class="text-gray-400">Sin stock</span>
              @endforelse
            </td>
            <td class="px-3 py-2 text-right space-x-2">
              <a class="text-blue-600 hover:underline" href="{{ route('products.edit', $product) }}">Editar</a>
              <form action="{{ route('products.destroy', $product) }}" method="post" class="inline">
                @csrf
                @method('delete')
                <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('¿Eliminar este producto?')">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="px-3 py-4 text-center text-gray-500">No se encontraron productos.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $products->links() }}
  </div>
@endsection
