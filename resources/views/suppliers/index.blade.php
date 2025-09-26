@extends('layouts.app')

@section('title', 'Proveedores')

@section('content')
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold text-gray-800">Proveedores</h1>
    <a href="{{ route('suppliers.create') }}" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">Nuevo proveedor</a>
  </div>

  <div class="overflow-x-auto bg-white shadow rounded-lg">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-left">
        <tr>
          <th class="px-3 py-2">Nombre</th>
          <th class="px-3 py-2">Contacto</th>
          <th class="px-3 py-2">Teléfono</th>
          <th class="px-3 py-2">Correo</th>
          <th class="px-3 py-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($suppliers as $supplier)
          <tr class="border-t">
            <td class="px-3 py-2">{{ $supplier->name }}</td>
            <td class="px-3 py-2">{{ $supplier->contact ?? '—' }}</td>
            <td class="px-3 py-2">{{ $supplier->phone ?? '—' }}</td>
            <td class="px-3 py-2">{{ $supplier->email ?? '—' }}</td>
            <td class="px-3 py-2 text-right space-x-2">
              <a href="{{ route('suppliers.edit', $supplier) }}" class="text-blue-600 hover:underline">Editar</a>
              <form action="{{ route('suppliers.destroy', $supplier) }}" method="post" class="inline">
                @csrf
                @method('delete')
                <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('¿Eliminar este proveedor?')">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-3 py-4 text-center text-gray-500">Aún no hay proveedores registrados.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $suppliers->links() }}
  </div>
@endsection
