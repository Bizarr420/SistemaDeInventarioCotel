<?php

namespace Tests\Feature;

use App\Models\AssetVerification;
use App\Models\FixedAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardRedesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_uses_consistent_life_and_latest_verification_metrics(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $expiredAsset = FixedAsset::create([
            'internal_code' => 'DASH-EXPIRED',
            'name_item' => 'Activo con vida útil vencida',
            'asset_status' => 'obsoleto',
            'obsolete_disposition_status' => 'vendido',
            'acquisition_date' => '2010-01-01',
            'useful_life_years' => 20,
            'expected_useful_life' => '2020-01-01',
        ]);
        $activeAsset = FixedAsset::create([
            'internal_code' => 'DASH-ACTIVE',
            'name_item' => 'Activo vigente verificado',
            'asset_status' => 'operativo',
            'acquisition_date' => '2020-01-01',
            'useful_life_years' => 30,
            'expected_useful_life' => '2050-01-01',
        ]);
        AssetVerification::create([
            'fixed_asset_id' => $activeAsset->id,
            'verified_by' => $user->id,
            'verified_at' => '2026-01-01',
            'status' => 'operativo',
            'deterioration_level' => 0,
        ]);
        AssetVerification::create([
            'fixed_asset_id' => $activeAsset->id,
            'verified_by' => $user->id,
            'verified_at' => '2026-10-01',
            'status' => 'falla',
            'deterioration_level' => 10,
        ]);

        $life = $expiredAsset->usefulLifeMetrics(Carbon::parse('2026-10-19'));
        $this->assertSame(0.0, $life['remaining_years']);
        $this->assertSame(100, $life['consumed_percentage']);
        $this->assertTrue($life['near_end']);

        $this->actingAs($user)
            ->get(route('fixed-assets.show', $expiredAsset))
            ->assertOk()
            ->assertSee('0.0 años')
            ->assertSee('100.00%');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Total de activos')
            ->assertSee('Activos operativos')
            ->assertSee('Activos que requieren atención')
            ->assertSee('Valor bruto · escenario')
            ->assertSee('Depreciación acumulada simulada')
            ->assertSee('Valor neto del escenario')
            ->assertSee('Escenario SICAT simulado')
            ->assertSee('Vida útil restante ≤ 2 años (incluye vencidos)')
            ->assertSee('Con diferencias en la última')
            ->assertSee('1 con verificación registrada; 0 sin diferencias')
            ->assertSee('Inventario de productos')
            ->assertSee('data-analysis-tab="department"', false)
            ->assertSee('data-analysis-tab="branch"', false)
            ->assertSee('Actividad reciente')
            ->assertDontSee('Análisis por proveedor')
            ->assertDontSee('Valor estimado total');
    }

    public function test_near_end_alert_filter_includes_expired_assets_and_excludes_long_lived_assets(): void
    {
        FixedAsset::create([
            'internal_code' => 'LIFE-EXPIRED',
            'name_item' => 'Activo vencido dentro del filtro',
            'acquisition_date' => '2010-01-01',
            'useful_life_years' => 10,
            'expected_useful_life' => '2020-01-01',
        ]);
        FixedAsset::create([
            'internal_code' => 'LIFE-OK',
            'name_item' => 'Activo fuera del filtro',
            'acquisition_date' => '2020-01-01',
            'useful_life_years' => 30,
            'expected_useful_life' => '2050-01-01',
        ]);
        $this->actingAs(User::factory()->create())
            ->get(route('fixed-assets.index', [
                'alert' => 'near_end_of_life',
                'as_of' => '2026-10-19',
            ]))
            ->assertOk()
            ->assertSee('Activo vencido dentro del filtro')
            ->assertDontSee('Activo fuera del filtro');
    }

    public function test_dashboard_rejects_a_cutoff_after_today(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard', [
                'year' => 2026,
                'cutoff' => now()->addDay()->toDateString(),
            ]))
            ->assertSessionHasErrors('cutoff');
    }
}
