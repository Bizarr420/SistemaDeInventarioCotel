<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Services\AuditEventService;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $user = $request->user();
        $request->session()->put('user_session_version', $user->session_version);
        $request->session()->put('last_activity_at', now()->timestamp);
        $user->forceFill(['last_login_at' => now()])->saveQuietly();
        app(AuditEventService::class)->record(
            module: 'authentication',
            action: 'login_success',
            entity: $user,
            description: 'Inicio de sesión exitoso.',
            request: $request,
        );

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        app(AuditEventService::class)->record(
            module: 'authentication',
            action: 'logout',
            entity: $user,
            description: 'Cierre de sesión.',
            request: $request,
        );
        $user?->forceFill(['last_logout_at' => now()])->saveQuietly();
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
