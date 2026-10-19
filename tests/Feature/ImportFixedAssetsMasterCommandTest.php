<?php

namespace Tests\Feature;

use App\Models\FixedAsset;
use App\Models\FixedAssetValuation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportFixedAssetsMasterCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_replaces_assets_and_imports_annual_values_from_the_master_sheet(): void
    {
        FixedAsset::create([
            'internal_code' => 'OLD-001',
            'name_item' => 'Activo anterior',
        ]);

        $this->artisan('fixed-assets:import-master', [
            '--replace' => true,
            '--force' => true,
        ])->assertSuccessful();

        $this->assertDatabaseCount('fixed_assets', 1750);
        $this->assertDatabaseCount('fixed_asset_valuations', 7000);
        $this->assertSame(35, \DB::table('categories')->where('name', 'like', 'Cuenta %')->count());
        $this->assertDatabaseMissing('fixed_assets', ['internal_code' => 'OLD-001']);
        $this->assertDatabaseHas('fixed_assets', [
            'internal_code' => 'TER-001',
            'name_item' => 'Terreno / Predio Sector 1 - Central San Francisco - Av. Mariscal Santa Cruz',
            'acquisition_value' => 2525627.73,
            'current_accounting_value' => 2525627.73,
        ]);
        $this->assertDatabaseHas('categories', [
            'name' => 'Cuenta 23010301',
            'description' => 'TERRENOS DE EDIFICACIONES EN GENERAL',
        ]);
        $this->assertDatabaseHas('fixed_asset_valuations', [
            'fixed_asset_id' => FixedAsset::where('internal_code', 'TER-001')->value('id'),
            'valuation_year' => 2024,
            'amount' => 2525627.73,
        ]);

        $asset = FixedAsset::where('internal_code', 'TER-001')->firstOrFail();
        $this->assertSame([2023, 2024, 2025, 2026], $asset->annualValuations->pluck('valuation_year')->all());
        $this->assertInstanceOf(FixedAssetValuation::class, $asset->annualValuations->first());

        $this->actingAs(User::factory()->create())
            ->get(route('fixed-assets.show', $asset))
            ->assertOk()
            ->assertSee('Valores contables por año (Bs)')
            ->assertSee('2,525,627.73');
    }
}
