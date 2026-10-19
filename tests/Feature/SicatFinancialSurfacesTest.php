<?php

namespace Tests\Feature;

use App\Models\AccountingAdjustment;
use App\Models\FixedAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SicatFinancialSurfacesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_does_not_infer_accounting_depreciation_from_asset_profile_values(): void
    {
        FixedAsset::create([
            'internal_code' => 'DASH-001',
            'name_item' => 'Activo para dashboard',
            'acquisition_value' => '1000.00',
            'current_accounting_value' => '600.00',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Depreciación acumulada simulada')
            ->assertSee('Los valores no constituyen saldos contables oficiales.')
            ->assertDontSee('400.00');

        $this->get(route('sicat.overview'))
            ->assertOk()
            ->assertSee('Depreciación acumulada simulada');

        $this->get(route('sicat.executive'))
            ->assertOk()
            ->assertSee('Diferencia técnica frente al neto simulado');
    }

    public function test_accounting_adjustment_list_uses_the_fixed_asset_relation(): void
    {
        $asset = FixedAsset::create([
            'internal_code' => 'ADJ-001',
            'name_item' => 'Activo relacionado al ajuste',
        ]);
        $user = User::factory()->create();

        AccountingAdjustment::create([
            'fixed_asset_id' => $asset->id,
            'generated_by' => $user->id,
            'adjustment_type' => 'deterioration',
            'status' => 'posted',
            'recognized_amount' => '25.00',
        ]);

        $this->actingAs($user)
            ->get(route('accounting-adjustments.index'))
            ->assertOk()
            ->assertSee('Activo relacionado al ajuste')
            ->assertSee('ADJ-001')
            ->assertSee('No constituyen asientos del mayor contable');
    }
}
