<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Editar usuario: {{ $user->name }}</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-5 rounded-lg border bg-white p-6">
            @csrf @method('PUT')
            @include('users._form', ['user' => $user])
            <div class="flex justify-end gap-2"><a href="{{ route('users.index') }}" class="rounded-md border px-4 py-2 text-sm">Cancelar</a><button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Guardar cambios</button></div>
        </form>
    </div></div>
</x-app-layout>
