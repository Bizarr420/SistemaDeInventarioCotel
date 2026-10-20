<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AuditEventService;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check() || !auth()->user()->isAccessAllowed() || !in_array(auth()->user()->role, $roles, true)) {
            app(AuditEventService::class)->record(
                module: 'security',
                action: 'authorization_denied',
                description: 'Acceso denegado por restricción de rol.',
                result: 'rejected',
                reason: 'Rol requerido: '.implode(', ', $roles),
                request: $request,
                context: ['required_roles' => $roles],
            );
            abort(403, 'Acceso denegado.');
        }

        return $next($request);
    }
}