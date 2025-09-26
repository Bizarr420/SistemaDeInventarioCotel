@extends('layouts.app')

@section('title', 'Editar almacén')

@section('content')
  <div class="max-w-xl mx-auto bg-white shadow rounded-lg p-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-4">Editar almacén</h1>
    <form method="post" action="{{ route('warehouses.update', $warehouse) }}">
      @method('put')
      @include('warehouses._form', ['warehouse' => $warehouse])
    </form>
  </div>
@endsection
