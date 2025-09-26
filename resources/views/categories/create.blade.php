@extends('layouts.app')

@section('title', 'Nueva categoría')

@section('content')
  <div class="max-w-xl mx-auto bg-white shadow rounded-lg p-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-4">Nueva categoría</h1>
    <form method="post" action="{{ route('categories.store') }}">
      @include('categories._form')
    </form>
  </div>
@endsection
