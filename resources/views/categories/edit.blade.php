@extends('layouts.app')

@section('title', 'Editar categoría')

@section('content')
  <div class="max-w-xl mx-auto bg-white shadow rounded-lg p-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-4">Editar categoría</h1>
    <form method="post" action="{{ route('categories.update', $category) }}">
      @method('put')
      @include('categories._form', ['category' => $category])
    </form>
  </div>
@endsection
