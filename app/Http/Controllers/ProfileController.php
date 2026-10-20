<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Services\AuditEventService;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, AuditEventService $audit): RedirectResponse
    {
        $user = $request->user();
        $oldValues = $user->only(['name', 'email']);
        $user->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        DB::transaction(function () use ($user, $audit, $request, $oldValues): void {
            $user->save();
            $audit->record('users', 'profile_updated', $user, 'El usuario modificó los datos de su perfil.',
                $oldValues, $user->only(['name', 'email']), request: $request);
        });

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request, AuditEventService $audit): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user, $request, $audit): void {
            $user->forceFill([
                'status' => 'inactive',
                'session_version' => $user->session_version + 1,
                'remember_token' => \Illuminate\Support\Str::random(60),
            ])->saveQuietly();
            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            }
            $audit->record('users', 'self_deactivated', $user,
                'El usuario desactivó su propia cuenta.', ['status' => 'active'], ['status' => 'inactive'], request: $request);
        });
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
