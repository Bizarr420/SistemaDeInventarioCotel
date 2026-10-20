<?php

namespace App\Http\Controllers;

use App\Services\AuditEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SecuritySettingsController extends Controller
{
    public function index(): View
    {
        $settings = DB::table('system_settings')->pluck('value', 'key');

        return view('security.settings', compact('settings'));
    }

    public function update(Request $request, AuditEventService $audit): RedirectResponse
    {
        $values = $request->validate([
            'session_idle_minutes' => 'required|integer|min:5|max:1440',
            'audit_retention_days' => 'required|integer|min:30|max:36500',
        ]);
        $before = DB::table('system_settings')->whereIn('key', array_keys($values))->pluck('value', 'key')->all();
        DB::transaction(function () use ($values): void {
            foreach ($values as $key => $value) {
                DB::table('system_settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => (string) $value, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        });
        $audit->record('security', 'security_settings_updated', null, 'Configuración de seguridad actualizada.',
            $before, $values, request: $request);

        return back()->with('success', 'Configuración de seguridad actualizada.');
    }
}
