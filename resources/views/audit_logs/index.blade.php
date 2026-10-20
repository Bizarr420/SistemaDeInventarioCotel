<x-app-layout>
    <x-slot name="header"><div class="flex flex-wrap items-center justify-between gap-3"><h2 class="text-xl font-semibold text-slate-900">Auditoría y logs del sistema</h2>@can('audit.export')<a href="{{ route('audit_logs.export', request()->query()) }}" class="rounded-md bg-slate-900 px-3 py-2 text-sm font-semibold text-white">Exportar CSV</a>@endcan</div></x-slot>
    <div class="py-8"><div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
        <form method="GET" class="grid gap-3 rounded-lg border bg-white p-4 sm:grid-cols-2 lg:grid-cols-4">
            <label class="text-xs font-medium text-slate-600">Usuario<select name="user_id" class="mt-1 block w-full rounded-md border-slate-300 text-sm"><option value="">Todos</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(($filters['user_id'] ?? '') == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
            <label class="text-xs font-medium text-slate-600">Desde<input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="mt-1 block w-full rounded-md border-slate-300 text-sm"></label>
            <label class="text-xs font-medium text-slate-600">Hasta<input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="mt-1 block w-full rounded-md border-slate-300 text-sm"></label>
            <label class="text-xs font-medium text-slate-600">Módulo<select name="module" class="mt-1 block w-full rounded-md border-slate-300 text-sm"><option value="">Todos</option>@foreach($modules as $module)<option @selected(($filters['module'] ?? '') === $module)>{{ $module }}</option>@endforeach</select></label>
            <label class="text-xs font-medium text-slate-600">Acción<select name="action" class="mt-1 block w-full rounded-md border-slate-300 text-sm"><option value="">Todas</option>@foreach($actions as $action)<option @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>@endforeach</select></label>
            <label class="text-xs font-medium text-slate-600">IP<input type="text" name="ip_address" value="{{ $filters['ip_address'] ?? '' }}" placeholder="Dirección IP" class="mt-1 block w-full rounded-md border-slate-300 text-sm"></label>
            <label class="text-xs font-medium text-slate-600">Resultado<select name="result" class="mt-1 block w-full rounded-md border-slate-300 text-sm"><option value="">Todos</option>@foreach(['success' => 'Correcto', 'rejected' => 'Rechazado', 'failed' => 'Fallido'] as $value => $label)<option value="{{ $value }}" @selected(($filters['result'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
            <label class="text-xs font-medium text-slate-600">Tipo de registro<input name="entity_type" value="{{ $filters['entity_type'] ?? '' }}" class="mt-1 block w-full rounded-md border-slate-300 text-sm"></label>
            <label class="text-xs font-medium text-slate-600">ID del registro<input name="entity_id" value="{{ $filters['entity_id'] ?? '' }}" class="mt-1 block w-full rounded-md border-slate-300 text-sm"></label>
            <label class="text-xs font-medium text-slate-600 sm:col-span-2">Búsqueda<input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Descripción, usuario, acción, IP..." class="mt-1 block w-full rounded-md border-slate-300 text-sm"></label>
            <div class="flex items-end gap-2"><button class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white">Filtrar</button><a href="{{ route('audit_logs.index') }}" class="rounded-md border px-4 py-2 text-sm">Limpiar</a></div>
        </form>
        <div class="overflow-x-auto rounded-lg border bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-3 py-3">Fecha y hora</th><th class="px-3 py-3">Usuario</th><th class="px-3 py-3">Módulo / acción</th><th class="px-3 py-3">Registro</th><th class="px-3 py-3">IP / navegador</th><th class="px-3 py-3">Resultado</th><th class="px-3 py-3">Detalle</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                    <tr>
                        <td class="whitespace-nowrap px-3 py-3">{{ $log->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}<p class="text-[10px] text-slate-500">UTC {{ $log->created_at?->utc()->format('Y-m-d H:i:s') }}</p></td>
                        <td class="px-3 py-3">{{ $log->user?->name ?? 'Sistema' }}</td>
                        <td class="px-3 py-3">{{ $log->module ?? '—' }}<p class="text-xs text-slate-500">{{ $log->action ?? $log->event }}</p><p class="max-w-64 text-xs text-slate-600">{{ $log->description }}</p></td>
                        <td class="px-3 py-3">{{ class_basename($log->entity_type ?: $log->auditable_type) }} #{{ $log->entity_id ?: $log->auditable_id }}</td>
                        <td class="px-3 py-3">{{ $log->ip_address ?? '—' }}<p class="max-w-48 truncate text-xs text-slate-500">{{ $log->browser ?? $log->user_agent }}</p></td>
                        <td class="px-3 py-3"><span class="rounded-full px-2 py-1 text-xs {{ $log->result === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $log->result ?? 'success' }}</span></td>
                        <td class="px-3 py-3"><a href="{{ route('audit_logs.show', $log) }}" class="font-medium text-blue-700 hover:underline">Ver evento</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-3 py-8 text-center text-slate-500">No hay eventos con esos filtros.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div class="border-t p-4">{{ $logs->links() }}</div>
        </div>
        <p class="text-xs text-slate-500">Los eventos se encadenan con hashes para ayudar a detectar alteraciones. Esto no impide que un administrador con acceso directo a la base de datos modifique o elimine la cadena.</p>
    </div></div>
</x-app-layout>
