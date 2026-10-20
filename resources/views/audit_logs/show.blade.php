<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><h2 class="text-xl font-semibold text-slate-900">Detalle del evento #{{ $log->id }}</h2><a href="{{ route('audit_logs.index') }}" class="text-sm text-blue-700">Volver a auditoría</a></div></x-slot>
    <div class="py-8"><div class="mx-auto max-w-5xl space-y-4 px-4 sm:px-6 lg:px-8">
        <section class="grid gap-4 rounded-lg border bg-white p-5 sm:grid-cols-2">
            @foreach(['Fecha local' => $log->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s T'), 'Fecha UTC' => $log->created_at?->utc()->format('Y-m-d\TH:i:s\Z'), 'Usuario' => $log->user?->name ?? 'Sistema', 'Módulo' => $log->module, 'Acción' => $log->action ?? $log->event, 'Entidad' => class_basename($log->entity_type ?: $log->auditable_type), 'ID de entidad' => $log->entity_id ?: $log->auditable_id, 'Resultado' => $log->result, 'Motivo' => $log->reason, 'IP observada' => $log->ip_address, 'Navegador' => $log->browser, 'Sistema operativo' => $log->operating_system, 'Tipo estimado de dispositivo' => $log->device_type, 'Ruta' => $log->route_name, 'Correlación' => $log->correlation_id] as $label => $value)
                <div><p class="text-xs font-medium uppercase text-slate-500">{{ $label }}</p><p class="mt-1 break-words text-sm text-slate-900">{{ $value ?: '—' }}</p></div>
            @endforeach
            <div class="sm:col-span-2"><p class="text-xs font-medium uppercase text-slate-500">Descripción</p><p class="mt-1 text-sm text-slate-900">{{ $log->description ?? '—' }}</p></div>
        </section>
        <div class="grid gap-4 md:grid-cols-2">
            <section class="rounded-lg border bg-white p-5"><h3 class="font-semibold">Valores anteriores</h3><pre class="mt-3 overflow-auto whitespace-pre-wrap text-xs">{{ json_encode($log->old_values ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></section>
            <section class="rounded-lg border bg-white p-5"><h3 class="font-semibold">Valores nuevos</h3><pre class="mt-3 overflow-auto whitespace-pre-wrap text-xs">{{ json_encode($log->new_values ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></section>
        </div>
        <section class="rounded-lg border bg-white p-5"><h3 class="font-semibold">Integridad y contexto</h3><p class="mt-2 break-all text-xs text-slate-600">Hash anterior: {{ $log->previous_hash ?? '—' }}<br>Hash del evento: {{ $log->event_hash ?? 'Evento legado, sin hash' }}</p><pre class="mt-3 overflow-auto whitespace-pre-wrap text-xs">{{ json_encode($log->context ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></section>
    </div></div>
</x-app-layout>
