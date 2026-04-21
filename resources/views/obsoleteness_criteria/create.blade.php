@extends('layouts.app')

@section('title','Crear Criterio de Obsolescencia')
@section('content')
<div class="max-w-3xl mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Agregar Criterio</h1>
    <form method="POST" action="{{ route('obsoleteness_criteria.store') }}">
        @csrf
        <div class="mb-4">
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border p-2" required>
        </div>
        <div class="mb-4">
            <label>Tipo</label>
            <select name="type" class="w-full border p-2" required>
                <option value="">--</option>
                <option value="end_of_support_days" @selected(old('type')=='end_of_support_days')>Fin de soporte (días)</option>
                <option value="operational_capacity_min" @selected(old('type')=='operational_capacity_min')>Capacidad operativa mínima</option>
                <option value="useful_life_below" @selected(old('type')=='useful_life_below')>Vida útil por debajo (%)</option>
            </select>
        </div>
        <div class="mb-4">
            <label>Parámetros (JSON)</label>
            <textarea name="parameters" class="w-full border p-2" rows="3">{{ old('parameters', json_encode(old('parameters', []))) }}</textarea>
            <small>Ejemplo: {"days":30} / {"min":20}</small>
        </div>
        <div class="mb-4">
            <label>Activo</label>
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
        </div>
        <button class="bg-green-600 text-white px-4 py-2 rounded">Guardar</button>
    </form>
</div>
@endsection