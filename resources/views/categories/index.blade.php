@extends('layouts.app')

@section('title', 'Categorías')

@section('content')
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold text-gray-800">Categorías</h1>
    <a href="{{ route('categories.create') }}" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">Nueva categoría</a>
  </div>

  <div class="overflow-x-auto bg-white text-gray-900 shadow rounded-lg">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-left">
        <tr>
          <th class="px-3 py-2">Nombre</th>
          <th class="px-3 py-2">Descripción</th>
          <th class="px-3 py-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($categories as $category)
          <tr class="border-t">
            <td class="px-3 py-2">{{ $category->name }}</td>
            <td class="px-3 py-2">{{ $category->description ?? '—' }}</td>
            <td class="px-3 py-2 text-right space-x-2">
              <a href="{{ route('categories.edit', $category) }}" class="text-blue-600 hover:underline">Editar</a>
              <form action="{{ route('categories.destroy', $category) }}" method="post" class="inline">
                @csrf
                @method('delete')
                <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('¿Eliminar esta categoría?')">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="px-3 py-4 text-center text-gray-500">Aún no hay categorías registradas.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $categories->links() }}
  </div>
@endsection
