@extends('layouts.app')

@section('title','Dashboard')
@section('content')
  <h1 class="text-2xl text-gray-900 font-bold mb-4">Lista de Inventario</h1>

  <div class="overflow-x-auto bg-orange-200 rounded-lg shadow">
    <table class="min-w-full text-sm text-left">
      <thead class="bg-orange-300 text-gray-900 uppercase text-xs">
        <tr>
          <th class="px-4 py-2">Ubicación</th>
          <th class="px-4 py-2">COD.INT.</th>
          <th class="px-4 py-2">Nro de parte</th>
          <th class="px-4 py-2">Item</th>
          <th class="px-4 py-2">CND</th>
          <th class="px-4 py-2">Descripción</th>
          <th class="px-4 py-2">Und</th>
          <th class="px-4 py-2">Stock actual</th>
          <th class="px-4 py-2">Observaciones</th>
        </tr>
      </thead>
      <tbody class="divide-y text-gray-700">
        @foreach($products as $product)
          @foreach($product->stocks as $stock)
            <tr>
              <td class="px-4 py-2">{{ $stock->warehouse->name ?? '-' }}</td>
              <td class="px-4 py-2">{{ $product->internal_code }}</td>
              <td class="px-4 py-2">{{ $product->part_number }}</td>
              <td class="px-4 py-2">{{ $product->item }}</td>
              <td class="px-4 py-2">{{ $product->cnd }}</td>
              <td class="px-4 py-2">{{ $product->description }}</td>
              <td class="px-4 py-2">{{ $product->unit }}</td>
              <td class="px-4 py-2 text-center font-semibold">
                {{ $stock->current_stock }}
              </td>
              <td class="px-4 py-2">{{ $product->note }}</td>
            </tr>
          @endforeach
          @if($product->stocks->isEmpty())
            {{-- Si no hay stock en ningún almacén, igual muestra fila --}}
            <tr>
              <td class="px-4 py-2">-</td>
              <td class="px-4 py-2">{{ $product->internal_code }}</td>
              <td class="px-4 py-2">{{ $product->part_number }}</td>
              <td class="px-4 py-2">{{ $product->item }}</td>
              <td class="px-4 py-2">{{ $product->cnd }}</td>
              <td class="px-4 py-2">{{ $product->description }}</td>
              <td class="px-4 py-2">{{ $product->unit }}</td>
              <td class="px-4 py-2 text-center font-semibold">0</td>
              <td class="px-4 py-2">{{ $product->note }}</td>
            </tr>
          @endif
        @endforeach
      </tbody>
    </table>
    <div class="mt-4">
      {{ $products->links() }}
    </div>
  </div>
@endsection
