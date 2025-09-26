<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  @vite(['resources/css/app.css','resources/js/app.js'])
  <title>@yield('title','Inventario')</title>
</head>
<body class="min-h-full bg-white text-slate-800 dark:bg-gray-100 dark:text-slate-100">
  @include('layouts.navigation')

  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    @if(session('ok'))
      <div class="mb-4 rounded-lg bg-green-50 text-green-800 px-4 py-2">
        {{ session('ok') }}
      </div>
    @endif

    @if(session('error'))
      <div class="mb-4 rounded-lg bg-red-50 text-red-800 px-4 py-2">
        {{ session('error') }}
      </div>
    @endif

    @if($errors->any())
      <div class="mb-4 rounded-lg bg-red-50 text-red-800 px-4 py-2">
        {{ $errors->first() }}
      </div>
    @endif

    @yield('content')
  </main>

  @stack('scripts')
</body>
</html>
