<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><h2 class="text-xl font-semibold text-slate-900">Sesiones y accesos recientes</h2><form method="POST" action="{{ route('users.sessions.revoke-others') }}">@csrf<button class="rounded-md bg-red-700 px-3 py-2 text-sm font-semibold text-white">Cerrar otras sesiones</button></form></div></x-slot>
    <div class="py-8"><div class="mx-auto max-w-7xl overflow-x-auto rounded-lg border bg-white px-4 sm:px-6 lg:px-8">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-3 py-3">Usuario</th><th class="px-3 py-3">IP observada</th><th class="px-3 py-3">Navegador / sistema reportado</th><th class="px-3 py-3">Última actividad</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($sessions as $session)
                <tr><td class="px-3 py-3">{{ $session->name ?? 'Sesión sin usuario' }}@if((int) $session->user_id === auth()->id()) <span class="text-xs text-emerald-700">(sesión actual)</span>@endif</td><td class="px-3 py-3">{{ $session->ip_address ?? 'No disponible' }}</td><td class="max-w-md px-3 py-3 text-xs text-slate-600">{{ $session->user_agent ?? 'No disponible' }}</td><td class="px-3 py-3">{{ \Illuminate\Support\Carbon::createFromTimestamp($session->last_activity)->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}</td></tr>
            @empty
                <tr><td colspan="4" class="px-3 py-8 text-center text-slate-500">No hay sesiones guardadas en la tabla de sesiones.</td></tr>
            @endforelse
            </tbody>
        </table>
        <p class="my-3 text-xs text-slate-500">No se conoce el nombre real del equipo. La información mostrada procede de la IP y del agente de usuario comunicados al servidor.</p>
        <div class="py-3">{{ $sessions->links() }}</div>
    </div></div>
</x-app-layout>
