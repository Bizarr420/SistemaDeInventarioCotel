<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Crear usuario</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('users.store') }}" class="space-y-5 rounded-lg border bg-white p-6">
            @csrf
            @include('users._form', ['user' => null])
            <div class="flex justify-end gap-2"><a href="{{ route('users.index') }}" class="rounded-md border px-4 py-2 text-sm">Cancelar</a><button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Crear usuario</button></div>
        </form>
    </div></div>
</x-app-layout>
