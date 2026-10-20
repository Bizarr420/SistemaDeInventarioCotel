<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-slate-900">Gestión de usuarios</h2>
            <div class="flex gap-2">
                <a href="{{ route('users.sessions') }}" class="rounded-md border px-3 py-2 text-sm">Sesiones</a>
                @can('roles.manage')
                    <a href="{{ route('roles.index') }}" class="rounded-md border px-3 py-2 text-sm">Roles y permisos</a>
                @endcan
                <a href="{{ route('users.create') }}" class="rounded-md bg-slate-900 px-3 py-2 text-sm font-semibold text-white">Nuevo usuario</a>
            </div>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if(session('success')) <p class="rounded-md bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('success') }}</p> @endif
            <form method="GET" class="grid gap-3 rounded-lg border bg-white p-4 sm:grid-cols-4">
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nombre, usuario o correo" class="rounded-md border-slate-300 text-sm">
                <select name="role" class="rounded-md border-slate-300 text-sm">
                    <option value="">Todos los roles</option>
                    @foreach(\App\Models\Role::orderBy('name')->get() as $role)
                        <option value="{{ $role->slug }}" @selected(($filters['role'] ?? '') === $role->slug)>{{ $role->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-md border-slate-300 text-sm">
                    <option value="">Todos los estados</option>
                    @foreach(['active' => 'Activa', 'inactive' => 'Inactiva', 'blocked' => 'Bloqueada'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="rounded-md bg-slate-800 px-3 py-2 text-sm font-medium text-white">Filtrar</button>
            </form>
            <div class="overflow-x-auto rounded-lg border bg-white">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr><th class="px-4 py-3">Usuario</th><th class="px-4 py-3">Rol / área</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Último acceso</th><th class="px-4 py-3">Acciones</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr>
                            <td class="px-4 py-3"><p class="font-medium text-slate-900">{{ $user->name }}</p><p class="text-xs text-slate-500">{{ $user->username ? '@'.$user->username.' · ' : '' }}{{ $user->email }}</p><p class="text-xs text-slate-500">{{ $user->phone }}</p></td>
                            <td class="px-4 py-3">{{ $user->roleDefinition?->name ?? $user->role }}<p class="text-xs text-slate-500">{{ $user->job_title }}{{ $user->department ? ' · '.$user->department : '' }}</p></td>
                            <td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs {{ $user->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ ['active' => 'Activa', 'inactive' => 'Inactiva', 'blocked' => 'Bloqueada'][$user->status] ?? $user->status }}</span>@if($user->access_expires_at)<p class="mt-1 text-xs text-slate-500">Vence {{ $user->access_expires_at->format('d/m/Y') }}</p>@endif</td>
                            <td class="px-4 py-3">{{ $user->last_login_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? 'Sin acceso' }}</td>
                            <td class="space-y-2 px-4 py-3">
                                @if($user->id !== auth()->id())
                                    <a class="block text-blue-700 hover:underline" href="{{ route('users.edit', $user) }}">Editar</a>
                                    <form method="POST" action="{{ route('users.password-reset', $user) }}">@csrf<button class="text-blue-700 hover:underline">Enviar enlace de restablecimiento</button></form>
                                    @if($user->status !== 'inactive')
                                        <form method="POST" action="{{ route('users.destroy', $user) }}">@csrf @method('DELETE')<button class="text-red-700 hover:underline" onclick="return confirm('¿Desactivar cuenta y revocar sus sesiones?')">Desactivar cuenta</button></form>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-500">Cuenta actual</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No hay usuarios para esos filtros.</td></tr>
                    @endforelse
                    </tbody>
                </table>
                <div class="border-t p-4">{{ $users->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
