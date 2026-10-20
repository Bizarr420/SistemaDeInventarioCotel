<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\FixedAsset;
use App\Models\User;
use App\Services\AuditEventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccessControlAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_users_with_explicit_permission_can_access_user_and_audit_routes(): void
    {
        $reader = User::factory()->create(['role' => 'consulta']);
        $this->actingAs($reader)->get(route('users.index'))->assertForbidden();
        $this->actingAs($reader)->get(route('audit_logs.index'))->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('users.index'))->assertOk();
        $this->actingAs($admin)->get(route('audit_logs.index'))->assertOk();
    }

    public function test_login_events_are_persisted_without_passwords(): void
    {
        $user = User::factory()->create(['email' => 'audit-user@example.test']);
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-secret'])->assertSessionHasErrors();
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'authentication',
            'action' => 'login_failed',
            'result' => 'failed',
            'ip_address' => '127.0.0.1',
        ]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect();
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'authentication',
            'action' => 'login_success',
            'user_id' => $user->id,
            'result' => 'success',
        ]);
        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertStringNotContainsString('wrong-secret', AuditLog::all()->toJson());
        $this->assertStringNotContainsString('password', strtolower(AuditLog::all()->toJson()));
    }

    public function test_audit_chain_detects_event_tampering(): void
    {
        $event = app(AuditEventService::class)->record(
            'testing',
            'tamper_check',
            description: 'Evento íntegro.',
            newValues: ['nested' => ['second' => 2, 'first' => 1]],
        );
        $this->assertSame([], app(AuditEventService::class)->verifyChain()['invalid_ids']);

        $event->forceFill(['description' => 'Evento alterado.'])->saveQuietly();
        $this->assertSame([$event->id], app(AuditEventService::class)->verifyChain()['invalid_ids']);
    }

    public function test_audit_csv_neutralizes_spreadsheet_formulas(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        app(AuditEventService::class)->record('testing', 'formula', description: '=HYPERLINK("https://example.test")');

        $response = $this->actingAs($admin)->get(route('audit_logs.export', ['module' => 'testing']));

        $this->assertStringContainsString("'=HYPERLINK", $response->streamedContent());
    }

    public function test_financial_note_printing_requires_permission_and_is_audited(): void
    {
        $reader = User::factory()->create(['role' => 'consulta']);
        $this->actingAs($reader)
            ->postJson(route('reports.financial-notes.print'), ['management' => '2026', 'cutoff_date' => '2026-03-01'])
            ->assertForbidden();

        $accountant = User::factory()->create(['role' => 'contable']);
        $this->actingAs($accountant)
            ->postJson(route('reports.financial-notes.print'), ['management' => '2026', 'cutoff_date' => '2026-03-01'])
            ->assertOk()
            ->assertJson(['recorded' => true]);

        $this->assertDatabaseHas('audit_logs', [
            'module' => 'reports',
            'action' => 'financial_notes_print',
            'user_id' => $accountant->id,
        ]);
    }

    public function test_superadmin_can_be_bootstrapped_from_trusted_console(): void
    {
        $user = User::factory()->create(['role' => 'consulta']);

        $this->artisan('users:grant-superadmin', ['email' => $user->email, '--force' => true])
            ->assertExitCode(0);

        $this->assertSame('superadmin', $user->fresh()->role);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'superadmin_granted',
            'entity_id' => (string) $user->id,
            'result' => 'success',
        ]);
    }

    public function test_non_superadmins_cannot_manage_superadmin_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $this->actingAs($admin)
            ->get(route('users.edit', $superadmin))
            ->assertForbidden();
        $this->actingAs($admin)
            ->delete(route('users.destroy', $superadmin))
            ->assertForbidden();
    }

    public function test_entity_change_records_structured_diffs_and_audit_is_rolled_back_with_operation(): void
    {
        $actor = User::factory()->create(['role' => 'admin']);
        $this->actingAs($actor);
        $asset = FixedAsset::create(['internal_code' => 'AUD-1', 'name_item' => 'Activo de auditoría', 'asset_status' => 'operativo']);
        $asset->update(['asset_status' => 'falla']);
        $event = AuditLog::query()->where('entity_type', FixedAsset::class)->where('entity_id', (string) $asset->id)->latest('id')->firstOrFail();
        $this->assertSame('operativo', $event->old_values['asset_status']);
        $this->assertSame('falla', $event->new_values['asset_status']);
        $this->assertNotNull($event->event_hash);

        try {
            DB::transaction(function () use ($actor): void {
                app(AuditEventService::class)->record(
                    'testing',
                    'rollback',
                    null,
                    'Este evento debe revertirse junto con su operación.',
                    newValues: ['password' => 'must-not-appear', 'status' => 'unsafe'],
                );
                throw new \RuntimeException('rollback');
            });
        } catch (\RuntimeException) {
        }

        $this->assertDatabaseMissing('audit_logs', ['action' => 'rollback']);
        $this->assertSame($actor->id, auth()->id());
        $this->assertStringNotContainsString('must-not-appear', AuditLog::all()->toJson());
    }

    public function test_audit_filters_pagination_details_and_export_are_authorized(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        app(AuditEventService::class)->record(
            'fixed-assets',
            'reviewed',
            null,
            'Revision de un activo',
            request: request(),
            context: ['account' => '1001'],
        );

        $this->actingAs($admin)->get(route('audit_logs.index', ['module' => 'fixed-assets', 'search' => 'Revision']))
            ->assertOk()
            ->assertSeeText('Revision de un activo');
        $this->actingAs($admin)->get(route('audit_logs.export', ['module' => 'fixed-assets']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'audit',
            'action' => 'export',
            'user_id' => $admin->id,
        ]);
    }

    public function test_inactive_accounts_are_denied_and_admin_deactivation_revokes_database_sessions(): void
    {
        config(['session.driver' => 'database']);
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'consulta']);
        DB::table('sessions')->insert([
            'id' => 'revoked-session',
            'user_id' => $target->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);
        $this->actingAs($admin)->delete(route('users.destroy', $target))->assertRedirect(route('users.index'));
        $this->assertSame('inactive', $target->fresh()->status);
        $this->assertDatabaseMissing('sessions', ['id' => 'revoked-session']);
    }
}
