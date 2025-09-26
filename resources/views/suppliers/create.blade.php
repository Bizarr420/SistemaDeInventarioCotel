@extends('layouts.app')

@section('title', 'Nuevo proveedor')

@section('content')
  <div class="max-w-xl mx-auto bg-white shadow rounded-lg p-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-4">Nuevo proveedor</h1>
    <form method="post" action="{{ route('suppliers.store') }}">
      @include('suppliers._form')
    </form>
  </div>
@endsection
