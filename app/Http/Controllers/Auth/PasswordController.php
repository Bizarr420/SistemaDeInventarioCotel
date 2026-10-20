<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Services\AuditEventService;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request, AuditEventService $audit): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => \Illuminate\Support\Str::random(60),
            'session_version' => $request->user()->session_version + 1,
        ])->save();
        $request->session()->put('user_session_version', $request->user()->session_version);
        $audit->record('authentication', 'password_changed', $request->user(),
            'El usuario cambió su contraseña.', request: $request);

        return back()->with('status', 'password-updated');
    }
}
