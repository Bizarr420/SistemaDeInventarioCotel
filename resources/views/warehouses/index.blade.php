@extends('layouts.app')

@section('title', 'Almacenes')

@section('content')
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold text-gray-800">Almacenes</h1>
    <a href="{{ route('warehouses.create') }}" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">Nuevo almacén</a>
  </div>

  <div class="overflow-x-auto text-gray-900 bg-white shadow rounded-lg">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-left">
        <tr>
          <th class="px-3 py-2">Nombre</th>
          <th class="px-3 py-2">Código</th>
          <th class="px-3 py-2">Ubicación</th>
          <th class="px-3 py-2 text-center">Stock total</th>
          <th class="px-3 py-2 text-center">Productos distintos</th>
          <th class="px-3 py-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($warehouses as $warehouse)
          <tr class="border-t text-gray-700">
            <td class="px-3 py-2 font-medium text-gray-800">{{ $warehouse->name }}</td>
            <td class="px-3 py-2">{{ $warehouse->code }}</td>
            <td class="px-3 py-2">{{ $warehouse->location ?? '—' }}</td>
            <td class="px-3 py-2 text-center font-semibold text-gray-900">{{ $warehouse->total_stock ?? 0 }}</td>
            <td class="px-3 py-2 text-center">{{ $warehouse->stocks_count }}</td>
            <td class="px-3 py-2 text-right space-x-2">
              <a href="{{ route('warehouses.edit', $warehouse) }}" class="text-blue-600 hover:underline">Editar</a>
              <form action="{{ route('warehouses.destroy', $warehouse) }}" method="post" class="inline">
                @csrf
                @method('delete')
                <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('¿Eliminar este almacén?')">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-3 py-4 text-center text-gray-500">Aún no hay almacenes registrados.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $warehouses->links() }}
  </div>
@endsection
