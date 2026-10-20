<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && (! $user->isAccessAllowed()
            || (int) $request->session()->get('user_session_version', $user->session_version) !== (int) $user->session_version)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'La cuenta está inactiva, bloqueada o vencida.',
            ]);
        }
        if ($user) {
            $idleMinutes = (int) DB::table('system_settings')->where('key', 'session_idle_minutes')->value('value');
            $idleMinutes = $idleMinutes > 0 ? $idleMinutes : (int) config('session.lifetime');
            $lastActivity = (int) $request->session()->get('last_activity_at', now()->timestamp);
            if (now()->timestamp - $lastActivity > $idleMinutes * 60) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors(['email' => 'La sesión expiró por inactividad.']);
            }
            $request->session()->put('last_activity_at', now()->timestamp);
            $request->session()->put('user_session_version', $user->session_version);
        }

        return $next($request);
    }
}
