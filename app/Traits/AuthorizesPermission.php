<?php

namespace App\Traits;

trait AuthorizesPermission
{
    protected function authorizePermission(string $permission): void
    {
        abort_unless(request()->user()?->hasPermission($permission), 403, 'Acceso denegado.');
    }

    protected function authorizeAnyPermission(array $permissions): void
    {
        abort_unless(
            collect($permissions)->contains(fn (string $permission): bool => request()->user()?->hasPermission($permission)),
            403,
            'Acceso denegado.'
        );
    }
}
