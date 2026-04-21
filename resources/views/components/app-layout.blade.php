<div class="min-h-screen bg-gray-50 text-slate-800">
    @include('layouts.navigation')

    @isset($header)
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                {{ $header }}
            </div>
        </header>
    @endisset

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

        {{ $slot }}
    </main>
</div>