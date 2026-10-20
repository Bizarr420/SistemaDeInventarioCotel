@php($value = fn ($key, $default = '') => old($key, $user?->$key ?? $default))
<div class="grid gap-4 sm:grid-cols-2">
    @foreach(['name' => 'Nombre completo', 'username' => 'Nombre de usuario', 'email' => 'Correo', 'phone' => 'Teléfono', 'job_title' => 'Cargo o función', 'department' => 'Departamento o área'] as $field => $label)
        <label class="block text-sm font-medium text-slate-700">{{ $label }}
            <input name="{{ $field }}" type="{{ $field === 'email' ? 'email' : 'text' }}" value="{{ $value($field) }}" @required($field === 'name' || $field === 'email') class="mt-1 block w-full rounded-md border-slate-300 text-sm">
            @error($field)<span class="text-xs text-red-700">{{ $message }}</span>@enderror
        </label>
    @endforeach
    <label class="block text-sm font-medium text-slate-700">Rol
        <select name="role" required class="mt-1 block w-full rounded-md border-slate-300 text-sm">
            @foreach($roles as $role)<option value="{{ $role->slug }}" @selected($value('role', $roles->first()?->slug) === $role->slug)>{{ $role->name }}</option>@endforeach
        </select>
        @error('role')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
    </label>
    <label class="block text-sm font-medium text-slate-700">Estado
        <select name="status" required class="mt-1 block w-full rounded-md border-slate-300 text-sm">
            @foreach(['active' => 'Activa', 'inactive' => 'Inactiva', 'blocked' => 'Bloqueada'] as $status => $label)<option value="{{ $status }}" @selected($value('status', 'active') === $status)>{{ $label }}</option>@endforeach
        </select>
    </label>
    <label class="block text-sm font-medium text-slate-700">Vencimiento de acceso (opcional)
        <input type="date" name="access_expires_at" value="{{ $user?->access_expires_at?->format('Y-m-d') ?? old('access_expires_at') }}" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
    </label>
    @unless($user)
        <label class="block text-sm font-medium text-slate-700">Contraseña inicial
            <input name="password" type="password" required autocomplete="new-password" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
            @error('password')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
        </label>
        <label class="block text-sm font-medium text-slate-700">Confirmar contraseña
            <input name="password_confirmation" type="password" required autocomplete="new-password" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
        </label>
    @endunless
</div>
<p class="text-xs text-slate-500">La aplicación no muestra, exporta ni audita contraseñas ni hashes. El nombre de computadora no está disponible de forma confiable en el navegador.</p>
