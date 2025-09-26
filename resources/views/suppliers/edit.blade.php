@extends('layouts.app')

@section('title', 'Editar proveedor')

@section('content')
  <div class="max-w-xl mx-auto bg-white shadow rounded-lg p-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-4">Editar proveedor</h1>
    <form method="post" action="{{ route('suppliers.update', $supplier) }}">
      @method('put')
      @include('suppliers._form', ['supplier' => $supplier])
    </form>
  </div>
@endsection
