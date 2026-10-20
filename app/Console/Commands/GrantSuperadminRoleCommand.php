<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AuditEventService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GrantSuperadminRoleCommand extends Command
{
    protected $signature = 'users:grant-superadmin {email : Existing account email} {--force : Skip confirmation}';

    protected $description = 'Grant the superadministrator role to an existing user from the trusted server console';

    public function handle(AuditEventService $audit): int
    {
        $user = User::where('email', $this->argument('email'))->first();
        if (! $user) {
            $this->error('No existe un usuario con ese correo.');

            return self::FAILURE;
        }
        if (! $user->email_verified_at) {
            $this->error('La cuenta debe tener el correo verificado antes de recibir privilegios administrativos.');

            return self::FAILURE;
        }
        if (! $this->option('force') && ! $this->confirm("Grant superadmin to {$user->email}?")) {
            $this->info('No se realizaron cambios.');

            return self::SUCCESS;
        }

        $previousRole = $user->role;
        DB::transaction(function () use ($user, $audit, $previousRole): void {
            $user->forceFill([
                'role' => 'superadmin',
                'status' => 'active',
                'access_expires_at' => null,
                'session_version' => $user->session_version + 1,
                'remember_token' => \Illuminate\Support\Str::random(60),
            ])->saveQuietly();
            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            }
            $audit->record('security', 'superadmin_granted', $user,
                'Rol de superadministrador otorgado desde la consola confiable.',
                ['role' => $previousRole], ['role' => 'superadmin', 'sessions_revoked' => true],
                context: ['actor' => 'system_console']);
        });

        $this->info("Rol de superadministrador otorgado a {$user->email}; las sesiones anteriores fueron revocadas.");

        return self::SUCCESS;
    }
}
