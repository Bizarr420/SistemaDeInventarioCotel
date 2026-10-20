<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Historial de {{ $entityType }} #{{ $entityId }}</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-7xl space-y-3 px-4 sm:px-6 lg:px-8">
        @forelse($logs as $log)
            <a href="{{ route('audit_logs.show', $log) }}" class="block rounded-lg border bg-white p-4 hover:border-slate-400">
                <div class="flex flex-wrap justify-between gap-2"><p class="font-semibold text-slate-900">{{ $log->description ?? $log->action ?? $log->event }}</p><time class="text-xs text-slate-500">{{ $log->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s T') }}</time></div>
                <p class="mt-1 text-xs text-slate-600">Actor: {{ $log->user?->name ?? 'Sistema' }} · {{ $log->ip_address ?? 'IP no disponible' }} · {{ $log->result ?? 'success' }}</p>
                <div class="mt-2 grid gap-2 text-xs sm:grid-cols-2"><pre class="overflow-auto whitespace-pre-wrap">Anterior: {{ json_encode($log->old_values ?? [], JSON_UNESCAPED_UNICODE) }}</pre><pre class="overflow-auto whitespace-pre-wrap">Nuevo: {{ json_encode($log->new_values ?? [], JSON_UNESCAPED_UNICODE) }}</pre></div>
            </a>
        @empty
            <p class="rounded-lg border bg-white p-8 text-center text-sm text-slate-500">No hay eventos registrados para esta entidad.</p>
        @endforelse
        {{ $logs->links() }}
    </div></div>
</x-app-layout>
