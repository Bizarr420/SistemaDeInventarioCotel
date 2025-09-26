@extends('layouts.app')

@section('title', 'Nuevo almacén')

@section('content')
  <div class="max-w-xl mx-auto bg-white shadow rounded-lg p-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-4">Nuevo almacén</h1>
    <form method="post" action="{{ route('warehouses.store') }}">
      @include('warehouses._form')
    </form>
  </div>
@endsection
