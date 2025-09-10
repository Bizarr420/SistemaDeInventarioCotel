@extends('layouts.app')

@section('title','Dashboard')
@section('content')
  <h1 class="text-2xl font-bold mb-4">Dashboard</h1>
  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="p-4 rounded-xl bg-white shadow-sm dark:bg-slate-900">
      <p class="text-sm text-slate-500">Productos</p>
      <p class="text-2xl font-semibold">0</p>
    </div>
    <div class="p-4 rounded-xl bg-white shadow-sm dark:bg-slate-900">
      <p class="text-sm text-slate-500">Stock total</p>
      <p class="text-2xl font-semibold">0</p>
    </div>
  </div>
@endsection
