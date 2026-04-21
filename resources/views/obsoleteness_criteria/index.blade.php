@extends('layouts.app')

@section('title','Criterios de Obsolescencia')
@section('content')
<div class="max-w-7xl mx-auto p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Criterios de Obsolescencia</h1>
        <a href="{{ route('obsoleteness_criteria.create') }}" class="bg-blue-500 text-white px-3 py-2 rounded">Nuevo criterio</a>
    </div>

    <table class="w-full border">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">Nombre</th>
                <th class="p-2">Tipo</th>
                <th class="p-2">Parámetros</th>
                <th class="p-2">Activo</th>
                <th class="p-2">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($criteria as $item)
                <tr>
                    <td class="p-2">{{ $item->name }}</td>
                    <td class="p-2">{{ $item->type }}</td>
                    <td class="p-2"><pre>{{ json_encode($item->parameters) }}</pre></td>
                    <td class="p-2">{{ $item->is_active ? 'Sí' : 'No' }}</td>
                    <td class="p-2">
                        <a class="text-blue-600" href="{{ route('obsoleteness_criteria.edit', $item) }}">Editar</a>
                        <form action="{{ route('obsoleteness_criteria.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Eliminar criterio?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 ml-2">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection