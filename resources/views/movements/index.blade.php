@extends('layouts.app')

@section('title', 'Movimientos de inventario')

@section('content')
  <div class="text-slate-800 dark:text-slate-800">
    <h1 class="text-2xl font-semibold text-gray-800 mb-4">Movimientos de inventario</h1>

  <form method="post" action="{{ route('movements.store') }}" class="grid sm:grid-cols-5 gap-3 mb-6 bg-white p-4 rounded-lg shadow">
    @csrf
    <select name="product_id" class="border rounded px-2 py-2" required>
      <option value="">Producto…</option>
      @foreach($products as $product)
        <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->internal_code }} - {{ $product->name_item }}</option>
      @endforeach
    </select>
    <select name="warehouse_id" class="border rounded px-2 py-2" required>
      <option value="">Almacén…</option>
      @foreach($warehouses as $warehouse)
        <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->code }} - {{ $warehouse->name }}</option>
      @endforeach
    </select>
    <select name="type" class="border rounded px-2 py-2" required>
      <option value="in" @selected(old('type') === 'in')>Entrada</option>
      <option value="out" @selected(old('type') === 'out')>Salida</option>
    </select>
    <input name="quantity" type="number" min="1" class="border rounded px-2 py-2" placeholder="Cantidad" value="{{ old('quantity') }}" required>
    <button class="px-3 py-2 bg-orange-600 text-white rounded">Registrar</button>
    <div class="sm:col-span-5">
      <input name="note" class="border rounded px-2 py-2 w-full" placeholder="Observación (opcional)" value="{{ old('note') }}">
    </div>
  </form>

  <div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-left">
        <tr>
          <th class="px-3 py-2">Fecha</th>
          <th class="px-3 py-2">Producto</th>
          <th class="px-3 py-2">Almacén</th>
          <th class="px-3 py-2 text-center">Tipo</th>
          <th class="px-3 py-2 text-right">Cantidad</th>
          <th class="px-3 py-2">Usuario</th>
          <th class="px-3 py-2">Nota</th>
        </tr>
      </thead>
      <tbody>
        @forelse($movements as $movement)
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
            <td class="px-3 py-2">{{ $movement->user->name ?? '—' }}</td>
            <td class="px-3 py-2">{{ $movement->note ?? '—' }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-3 py-4 text-center text-gray-500">Aún no se registraron movimientos.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $movements->links() }}
  </div>
  </div>
@endsection
