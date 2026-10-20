<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('roles.index', [
            'roles' => Role::with('permissions')->withCount('permissions')->orderBy('name')->get(),
            'permissions' => Permission::orderBy('module')->orderBy('name')->get()->groupBy('module'),
        ]);
    }

    public function store(Request $request, AuditEventService $audit): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'slug' => 'required|string|max:80|alpha_dash|unique:roles,slug',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);
        $permissionIds = $this->authorizedPermissionIds($request->user(), $validated['permissions'] ?? []);
        $role = DB::transaction(function () use ($validated, $permissionIds, $audit, $request): Role {
            $role = Role::create(collect($validated)->except('permissions')->all());
            $role->permissions()->sync($permissionIds);
            $audit->record('security', 'role_created', $role, 'Rol y permisos creados.',
                null, ['slug' => $role->slug, 'permission_ids' => $permissionIds], request: $request);

            return $role;
        });

        return back()->with('success', 'Rol creado.');
    }

    public function update(Request $request, Role $role, AuditEventService $audit): RedirectResponse
    {
        abort_if($role->slug === 'superadmin' && $request->user()->role !== 'superadmin', 403);
        abort_if($role->slug === $request->user()->role && $request->user()->role !== 'superadmin', 403,
            'No puede modificar los permisos del rol que tiene asignado.');
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);
        $permissionIds = $this->authorizedPermissionIds($request->user(), $validated['permissions'] ?? []);
        $before = ['name' => $role->name, 'description' => $role->description, 'permissions' => $role->permissions->pluck('slug')->all()];
        DB::transaction(function () use ($role, $validated, $permissionIds, $audit, $request, $before): void {
            $role->update(collect($validated)->except('permissions')->all());
            $role->permissions()->sync($permissionIds);
            $audit->record('security', 'role_updated', $role, 'Permisos del rol modificados.',
                $before, ['name' => $role->name, 'description' => $role->description, 'permission_ids' => $permissionIds],
                request: $request);
        });

        return back()->with('success', 'Permisos del rol actualizados.');
    }

    private function authorizedPermissionIds($actor, array $permissionIds): array
    {
        $allowed = Permission::query()
            ->whereIn('id', $permissionIds)
            ->get()
            ->filter(fn (Permission $permission): bool => $actor->hasPermission($permission->slug))
            ->pluck('id')
            ->all();
        abort_if(count($allowed) !== count(array_unique($permissionIds)), 403, 'No puede asignar permisos que no posee.');

        return $allowed;
    }
}
