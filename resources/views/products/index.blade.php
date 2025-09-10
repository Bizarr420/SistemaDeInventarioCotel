{{-- resources/views/products/index.blade.php --}}
@extends('layouts.app')
@section('title','Productos')
@section('content')
  <div class="flex items-center justify-between mb-4">
    <form>
      <input name="q" value="{{ $q }}" placeholder="Buscar nombre/sku"
             class="px-3 py-2 rounded-lg border" />
      <button class="px-3 py-2 rounded-lg bg-blue-600 text-white">Buscar</button>
    </form>
    <a href="{{ route('products.create') }}" class="px-3 py-2 rounded-lg bg-green-600 text-white">Nuevo</a>
  </div>
  <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
    <table class="min-w-full text-sm">
      <thead><tr class="bg-slate-50">
        <th class="px-3 py-2 text-left">Nombre</th>
        <th class="px-3 py-2">SKU</th>
        <th class="px-3 py-2">Categoría</th>
        <th class="px-3 py-2">Stock</th>
        <th class="px-3 py-2">Acciones</th>
      </tr></thead>
      <tbody>
      @foreach($items as $p)
        <tr class="border-t">
          <td class="px-3 py-2 text-left">{{ $p->name }}</td>
          <td class="px-3 py-2 text-center">{{ $p->sku }}</td>
          <td class="px-3 py-2 text-center">{{ $p->category->name ?? '-' }}</td>
          <td class="px-3 py-2 text-center font-semibold">{{ $p->stock }}</td>
          <td class="px-3 py-2 text-center">
            <a class="text-blue-600" href="{{ route('products.edit',$p) }}">Editar</a>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $items->links() }}</div>
@endsection
