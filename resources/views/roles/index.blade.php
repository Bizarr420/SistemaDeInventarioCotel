<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Roles y permisos</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
        @if(session('success'))<p class="rounded-md bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('success') }}</p>@endif
        <section class="rounded-lg border bg-white p-5">
            <h3 class="font-semibold text-slate-900">Crear rol adicional</h3>
            <form method="POST" action="{{ route('roles.store') }}" class="mt-4 space-y-4">@csrf
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="text-sm">Nombre<input name="name" required class="mt-1 block w-full rounded-md border-slate-300 text-sm"></label>
                    <label class="text-sm">Identificador<input name="slug" required pattern="[A-Za-z0-9_-]+" class="mt-1 block w-full rounded-md border-slate-300 text-sm"></label>
                </div>
                <label class="block text-sm">Descripción<input name="description" class="mt-1 block w-full rounded-md border-slate-300 text-sm"></label>
                @foreach($permissions as $module => $modulePermissions)
                    <fieldset class="rounded-md border p-3">
                        <legend class="px-1 text-sm font-semibold capitalize text-slate-700">{{ $module }}</legend>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($modulePermissions as $permission)
                                @if(auth()->user()->hasPermission($permission->slug))
                                    <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="rounded border-slate-300">{{ $permission->name }}</label>
                                @endif
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
                <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Crear rol</button>
            </form>
        </section>
        <div class="space-y-4">
            @foreach($roles as $role)
                <form method="POST" action="{{ route('roles.update', $role) }}" class="rounded-lg border bg-white p-5">
                    @csrf @method('PUT')
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div><h3 class="font-semibold text-slate-900">{{ $role->name }} <span class="text-xs font-normal text-slate-500">({{ $role->slug }})</span></h3><p class="text-xs text-slate-500">{{ $role->description }} · {{ $role->permissions_count }} permisos</p></div>
                        @if($role->slug !== 'superadmin' || auth()->user()->role === 'superadmin')
                            <button class="rounded-md border px-3 py-1.5 text-sm font-medium hover:bg-slate-50">Guardar permisos</button>
                        @endif
                    </div>
                    <input type="hidden" name="name" value="{{ $role->name }}">
                    <input type="hidden" name="description" value="{{ $role->description }}">
                    <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($permissions as $module => $modulePermissions)
                            <fieldset class="rounded-md border p-3">
                                <legend class="px-1 text-xs font-semibold uppercase text-slate-500">{{ $module }}</legend>
                                <div class="space-y-1.5">
                                    @foreach($modulePermissions as $permission)
                                        @if(auth()->user()->hasPermission($permission->slug))
                                            <label class="flex items-center gap-2 text-xs text-slate-700"><input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked($role->permissions->contains('id', $permission->id)) @disabled($role->slug === 'superadmin' && auth()->user()->role !== 'superadmin') class="rounded border-slate-300">{{ $permission->name }}</label>
                                        @endif
                                    @endforeach
                                </div>
                            </fieldset>
                        @endforeach
                    </div>
                </form>
            @endforeach
        </div>
        <p class="text-xs text-slate-500">Un administrador no puede asignar permisos que no posee ni asignar el rol de superadministrador. Los cambios quedan auditados.</p>
    </div></div>
</x-app-layout>
