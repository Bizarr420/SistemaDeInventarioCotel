<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FixedAsset;
use App\Models\FixedAssetValuation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialNotesReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_financial_notes_use_database_valuations_and_do_not_assign_undated_2026_values_to_a_cutoff(): void
    {
        $category = Category::create([
            'name' => 'Cuenta 23010301',
            'description' => 'Terrenos',
        ]);
        $asset = FixedAsset::create([
            'internal_code' => 'TER-001',
            'name_item' => 'Terreno de prueba',
            'category_id' => $category->id,
            'quantity' => 1,
        ]);

        foreach ([
            2023 => ['amount' => '100.00', 'as_of_date' => null],
            2024 => ['amount' => '110.00', 'as_of_date' => null],
            2025 => ['amount' => '120.00', 'as_of_date' => null],
            2026 => ['amount' => '130.00', 'as_of_date' => null],
        ] as $year => $valuation) {
            FixedAssetValuation::create([
                'fixed_asset_id' => $asset->id,
                'valuation_year' => $year,
                'amount' => $valuation['amount'],
                'as_of_date' => $valuation['as_of_date'],
                'source' => 'Prueba',
            ]);
        }

        $this->actingAs(User::factory()->create())
            ->get(route('reports.financial-notes', [
                'management' => 'all',
                'cutoff_date' => '2026-10-19',
            ]))
            ->assertOk()
            ->assertSee('No hay saldos de cuenta registrados en la base para este rubro.')
            ->assertSee('2026 al 19/10/2026')
            ->assertSee('No se presentan como saldo al')
            ->assertSee('130.00')
            ->assertSee('100.00')
            ->assertSee('No disponible');
    }

    public function test_cutoff_date_cannot_be_after_today(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('reports.financial-notes', ['cutoff_date' => now()->addDay()->toDateString()]))
            ->assertRedirect();
    }

    public function test_2026_valuations_after_the_selected_cutoff_are_excluded(): void
    {
        $category = Category::create([
            'name' => 'Cuenta 23010301',
            'description' => 'Terrenos',
        ]);
        $asset = FixedAsset::create([
            'internal_code' => 'TER-FUTURE',
            'name_item' => 'Terreno con saldo posterior al corte',
            'category_id' => $category->id,
        ]);
        FixedAssetValuation::create([
            'fixed_asset_id' => $asset->id,
            'valuation_year' => 2026,
            'amount' => '130.00',
            'as_of_date' => '2026-10-20',
            'source' => 'Prueba',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('reports.financial-notes', [
                'cutoff_date' => '2026-10-19',
                'management' => '2026',
            ]))
            ->assertOk()
            ->assertSee('Se excluyen 1 valuaciones fechadas después del corte seleccionado.')
            ->assertDontSee('Bs 130.00');
    }

    public function test_2026_valuations_on_or_before_the_selected_cutoff_are_included(): void
    {
        $category = Category::create([
            'name' => 'Cuenta 23010301',
            'description' => 'Terrenos',
        ]);
        $asset = FixedAsset::create([
            'internal_code' => 'TER-DATED',
            'name_item' => 'Terreno con saldo al corte',
            'category_id' => $category->id,
        ]);
        FixedAssetValuation::create([
            'fixed_asset_id' => $asset->id,
            'valuation_year' => 2026,
            'amount' => '130.00',
            'as_of_date' => '2026-10-18',
            'source' => 'Prueba',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('reports.financial-notes', [
                'cutoff_date' => '2026-10-19',
                'management' => '2026',
            ]))
            ->assertOk()
            ->assertSee('Saldo base más movimientos al corte')
            ->assertSee('130.00');
    }
}
