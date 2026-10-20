<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Configuración de seguridad</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-3xl space-y-4 px-4 sm:px-6 lg:px-8">
        @if(session('success'))<p class="rounded-md bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('success') }}</p>@endif
        <form method="POST" action="{{ route('security.settings.update') }}" class="space-y-5 rounded-lg border bg-white p-6">
            @csrf @method('PUT')
            <label class="block text-sm font-medium text-slate-700">Expiración de sesión por inactividad (minutos)
                <input type="number" name="session_idle_minutes" min="5" max="1440" required value="{{ old('session_idle_minutes', $settings['session_idle_minutes'] ?? config('session.lifetime')) }}" class="mt-1 block w-full rounded-md border-slate-300">
                @error('session_idle_minutes')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
            </label>
            <label class="block text-sm font-medium text-slate-700">Retención objetivo de auditoría (días)
                <input type="number" name="audit_retention_days" min="30" max="36500" required value="{{ old('audit_retention_days', $settings['audit_retention_days'] ?? 3650) }}" class="mt-1 block w-full rounded-md border-slate-300">
                @error('audit_retention_days')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
            </label>
            <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Guardar configuración</button>
        </form>
        <p class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">La retención se registra como política; no se eliminan automáticamente eventos encadenados. Para una retención efectiva debe exportarse y archivarse una copia protegida antes de depurar datos. Un administrador con acceso directo a la base de datos aún puede alterar la tabla.</p>
        <p class="text-xs text-slate-500">MFA no está activado: el proyecto no tiene un proveedor ni una biblioteca MFA configurados. La IP puede reflejar el proxy solo cuando ese proxy esté expresamente configurado como confiable.</p>
    </div></div>
</x-app-layout>
