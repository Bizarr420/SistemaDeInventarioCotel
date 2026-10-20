<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\AuditEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRules;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:120',
            'role' => 'nullable|string|max:60',
            'status' => 'nullable|in:active,inactive,blocked',
        ]);
        $users = User::query()
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            }))
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->where('role', $role))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('users.index', compact('users', 'filters'));
    }

    public function create(): View
    {
        return view('users.create', ['roles' => $this->assignableRoles()]);
    }

    public function store(Request $request, AuditEventService $audit): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:80|alpha_dash|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:40',
            'job_title' => 'nullable|string|max:120',
            'department' => 'nullable|string|max:120',
            'password' => ['required', 'confirmed', PasswordRules::defaults()],
            'role' => ['required', 'string', Rule::exists('roles', 'slug')],
            'status' => 'required|in:active,inactive,blocked',
            'access_expires_at' => 'nullable|date|after:today',
        ]);
        $this->ensureAssignableRole($request->user(), $validated['role']);

        $user = DB::transaction(function () use ($validated, $request, $audit): User {
            $user = User::create([
                ...collect($validated)->except('password')->all(),
                'password' => $validated['password'],
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);
            $audit->record('users', 'created', $user, 'Usuario creado.', null, $user->only([
                'name', 'username', 'email', 'phone', 'job_title', 'department', 'role', 'status', 'access_expires_at',
            ]), request: $request);

            return $user;
        });

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(User $user): View
    {
        abort_if($user->is(auth()->user()), 403, 'No puede cambiar su propio rol o estado desde esta pantalla.');
        $this->ensureTargetManageable(auth()->user(), $user);

        return view('users.edit', ['user' => $user, 'roles' => $this->assignableRoles()]);
    }

    public function update(Request $request, User $user, AuditEventService $audit): RedirectResponse
    {
        abort_if($user->is($request->user()), 403, 'No puede cambiar su propio rol o estado desde esta pantalla.');
        $this->ensureTargetManageable($request->user(), $user);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['nullable', 'string', 'max:80', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:40',
            'job_title' => 'nullable|string|max:120',
            'department' => 'nullable|string|max:120',
            'role' => ['required', 'string', Rule::exists('roles', 'slug')],
            'status' => 'required|in:active,inactive,blocked',
            'access_expires_at' => 'nullable|date|after:today',
        ]);
        $this->ensureAssignableRole($request->user(), $validated['role']);
        $this->ensureSuperadminRemainsAvailable($user, $validated['role'], $validated['status'], $validated['access_expires_at'] ?? null);
        $oldValues = $user->only(['name', 'username', 'email', 'phone', 'job_title', 'department', 'role', 'status', 'access_expires_at']);
        $revokeSessions = $validated['status'] !== 'active';
        $validated['updated_by'] = $request->user()->id;

        DB::transaction(function () use ($user, $validated, $revokeSessions, $audit, $request, $oldValues): void {
            $user->fill($validated);
            if ($revokeSessions) {
                $user->session_version++;
                $user->remember_token = \Illuminate\Support\Str::random(60);
            }
            $user->save();
            if ($revokeSessions && config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            }
            $audit->record('users', 'updated', $user, 'Usuario actualizado.',
                $oldValues, $user->only(['name', 'username', 'email', 'phone', 'job_title', 'department', 'role', 'status', 'access_expires_at']),
                request: $request);
        });

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user, Request $request, AuditEventService $audit): RedirectResponse
    {
        abort_if($user->is($request->user()), 403, 'No puede desactivar su propia cuenta.');
        $this->ensureTargetManageable($request->user(), $user);
        $this->ensureSuperadminRemainsAvailable($user, $user->role, 'inactive', null);
        $before = $user->only(['role', 'status']);
        DB::transaction(function () use ($user, $audit, $request, $before): void {
            $user->forceFill([
                'status' => 'inactive',
                'session_version' => $user->session_version + 1,
                'remember_token' => \Illuminate\Support\Str::random(60),
                'updated_by' => auth()->id(),
            ])->save();
            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            }
            $audit->record('users', 'deactivated', $user, 'Cuenta de usuario desactivada; las sesiones existentes fueron revocadas.',
                $before, ['status' => $user->status, 'session_revoked' => true], request: $request);
        });

        return redirect()->route('users.index')->with('success', 'La cuenta fue desactivada y sus sesiones revocadas.');
    }

    public function sendPasswordReset(User $user, Request $request, AuditEventService $audit): RedirectResponse
    {
        $this->ensureTargetManageable($request->user(), $user);
        $status = Password::sendResetLink(['email' => $user->email]);
        $audit->record('users', 'password_reset_link_sent', $user,
            'Solicitud de enlace de restablecimiento de contraseña.', result: 'success', request: $request);

        return back()->with('success', __($status));
    }

    public function sessions(Request $request, AuditEventService $audit): View
    {
        abort_unless(config('session.driver') === 'database', 404);
        $audit->record(
            'security',
            'sessions_viewed',
            null,
            'Consulta de sesiones activas.',
            request: $request,
        );
        $sessions = DB::table(config('session.table', 'sessions'))
            ->leftJoin('users', 'users.id', '=', config('session.table', 'sessions').'.user_id')
            ->orderByDesc('last_activity')
            ->paginate(30);

        return view('users.sessions', compact('sessions'));
    }

    public function revokeOtherSessions(Request $request, AuditEventService $audit): RedirectResponse
    {
        abort_unless(config('session.driver') === 'database', 404);
        $deleted = DB::table(config('session.table', 'sessions'))
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();
        $audit->record('security', 'other_sessions_revoked', $request->user(),
            'Otras sesiones del usuario fueron cerradas.', newValues: ['sessions_revoked' => $deleted],
            request: $request);

        return back()->with('success', "Se cerraron {$deleted} otras sesiones.");
    }

    private function assignableRoles()
    {
        $roles = Role::query()->orderBy('name')->get();
        if (auth()->user()?->role !== 'superadmin') {
            $roles = $roles->reject(fn (Role $role): bool => $role->slug === 'superadmin')->values();
        }

        return $roles;
    }

    private function ensureAssignableRole(User $actor, string $role): void
    {
        abort_if($role === 'superadmin' && $actor->role !== 'superadmin', 403, 'Solo un superadministrador puede asignar ese rol.');
    }

    private function ensureTargetManageable(User $actor, User $target): void
    {
        abort_if($target->role === 'superadmin' && $actor->role !== 'superadmin', 403,
            'Solo un superadministrador puede administrar otra cuenta superadministradora.');
    }

    private function ensureSuperadminRemainsAvailable(User $target, string $newRole, string $newStatus, ?string $expiresAt): void
    {
        if ($target->role !== 'superadmin'
            || ! $target->isAccessAllowed()
            || ($newRole === 'superadmin' && $newStatus === 'active' && ($expiresAt === null || $expiresAt > now()->toDateTimeString()))) {
            return;
        }

        $otherActiveSuperadmins = User::query()
            ->where('role', 'superadmin')
            ->where('status', 'active')
            ->where('id', '!=', $target->id)
            ->where(function ($query): void {
                $query->whereNull('access_expires_at')->orWhere('access_expires_at', '>', now());
            })
            ->exists();
        abort_unless($otherActiveSuperadmins, 403, 'Debe permanecer al menos una cuenta superadministradora activa.');
    }
}
