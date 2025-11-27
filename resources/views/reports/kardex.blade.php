@extends('layouts.app')

@section('title', 'Kardex por producto')

@section('content')
  <div class="text-slate-800 dark:text-slate-800">
    <h1 class="text-2xl font-semibold text-gray-800 mb-4">Kardex por producto</h1>

  <form method="get" action="{{ route('reports.kardex') }}" class="mb-6 flex flex-col sm:flex-row gap-3">
    <select name="product_id" class="flex-1 rounded-lg border border-gray-300 px-3 py-2 focus:border-orange-500 focus:ring-orange-500" required>
      <option value="">Selecciona un producto</option>
      @foreach($products as $option)
        <option value="{{ $option->id }}" @selected(request('product_id') == $option->id)>{{ $option->internal_code }} - {{ $option->name_item }}</option>
      @endforeach
    </select>
    <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">Buscar</button>
  </form>

  @if($product)
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h2 class="text-xl font-semibold text-gray-800">{{ $product->name_item }}</h2>
      <p class="text-sm text-gray-500">Stock total actual: <span class="font-semibold text-gray-900">{{ $product->total_stock }}</span></p>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-left">
          <tr>
            <th class="px-3 py-2">Fecha</th>
            <th class="px-3 py-2">Almacén</th>
            <th class="px-3 py-2 text-center">Tipo</th>
            <th class="px-3 py-2 text-right">Cantidad</th>
            <th class="px-3 py-2 text-right">Saldo</th>
            <th class="px-3 py-2">Responsable</th>
            <th class="px-3 py-2">Nota</th>
          </tr>
        </thead>
        <tbody>
          @php($balance = 0)
          @forelse($product->movements as $movement)
            @php($balance += $movement->type === 'in' ? $movement->quantity : -$movement->quantity)
            <tr class="border-t">
              <td class="px-3 py-2">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
              <td class="px-3 py-2">{{ $movement->warehouse->name }}</td>
              <td class="px-3 py-2 text-center">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $movement->type === 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                  {{ $movement->type === 'in' ? 'Entrada' : 'Salida' }}
                </span>
              </td>
              <td class="px-3 py-2 text-right">{{ $movement->quantity }}</td>
              <td class="px-3 py-2 text-right font-semibold">{{ $balance }}</td>
              <td class="px-3 py-2">{{ $movement->user->name ?? '—' }}</td>
              <td class="px-3 py-2">{{ $movement->note ?? '—' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-3 py-4 text-center text-gray-500">Este producto aún no tiene movimientos.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  @endif
  </div>
@endsection
