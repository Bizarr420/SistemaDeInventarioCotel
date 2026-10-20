<?php

namespace App\Http\Middleware;

use App\Services\AuditEventService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        if (! $user || ! $user->isAccessAllowed() || ! $user->hasPermission($permission)) {
            app(AuditEventService::class)->record(
                module: 'security',
                action: 'authorization_denied',
                description: 'Acceso denegado a una función protegida.',
                result: 'rejected',
                reason: 'Permiso requerido: '.$permission,
                request: $request,
                context: ['permission' => $permission],
            );
            abort(403, 'Acceso denegado.');
        }

        return $next($request);
    }
}
