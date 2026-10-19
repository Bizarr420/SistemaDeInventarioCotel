<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FixedAsset;
use App\Models\User;
use App\Services\FinancialNotesReportService;
use App\Services\FixedAssetLedgerService;
use Database\Seeders\SicatFixedAssetSimulationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class SicatSimulationLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_simulation_reconciles_historical_notes_by_account_and_is_idempotent(): void
    {
        $this->createSimulationFixture();
        $this->runSimulationSeeder();

        $notes = app(FinancialNotesReportService::class)->buildAssetNotes(now()->toDateString());
        $this->assertSame('3694912310.46', $notes['note11']['totals']['2023']);
        $this->assertSame('3693575694.68', $notes['note11']['totals']['2024']);
        $this->assertSame('3311769226.93', $notes['note12']['totals']['2023']);
        $this->assertSame('3347599253.20', $notes['note12']['totals']['2024']);
        $this->assertSame(
            2,
            DB::table('fixed_asset_accounts')
                ->where('account_type', 'accumulated_depreciation')
                ->distinct('account_code')
                ->count('account_code')
        );
        foreach (['note11', 'note12'] as $noteName) {
            foreach ($notes[$noteName]['years'] as $year) {
                $rowTotal = array_sum(array_map(
                    fn (array $row): int => $this->toCents((string) ($row['values'][$year] ?? '0')),
                    $notes[$noteName]['rows']
                ));
                $this->assertSame(
                    $notes[$noteName]['totals'][$year] === null ? null : $this->toCents($notes[$noteName]['totals'][$year]),
                    $notes[$noteName]['totals'][$year] === null ? null : $rowTotal
                );
            }
        }

        $counts = $this->simulationCounts();
        $originalValues = DB::table('fixed_asset_valuation_originals')->orderBy('id')->get()->toArray();
        DB::table('fixed_asset_depreciation_parameters')->where('fixed_asset_id', FixedAsset::where('internal_code', 'SIM-BUILDING-01')->value('id'))
            ->update(['source' => 'APPROVED_MANUAL', 'useful_life_years' => 77]);
        $this->runSimulationSeeder();
        $this->assertSame($counts, $this->simulationCounts());
        $this->assertDatabaseHas('fixed_asset_depreciation_parameters', [
            'fixed_asset_id' => FixedAsset::where('internal_code', 'SIM-BUILDING-01')->value('id'),
            'source' => 'APPROVED_MANUAL',
            'useful_life_years' => 77,
        ]);
        $this->assertEquals($originalValues, DB::table('fixed_asset_valuation_originals')->orderBy('id')->get()->toArray());
        $this->assertSame(
            '3694912310.46',
            app(FinancialNotesReportService::class)->buildAssetNotes(now()->toDateString())['note11']['totals']['2023']
        );

        app(FixedAssetLedgerService::class)->assertOpenPeriod(2026);
        try {
            app(FixedAssetLedgerService::class)->assertOpenPeriod(2023);
            $this->fail('Closed historical period accepted an ordinary update.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('not open', $exception->getMessage());
        }
    }

    public function test_2026_movements_are_effective_dated_and_disposal_reverses_accumulated_depreciation(): void
    {
        [$land, $building] = $this->createSimulationFixture();
        $this->runSimulationSeeder();
        $user = User::factory()->create(['role' => 'admin']);
        $ledger = app(FixedAssetLedgerService::class);
        $before2023 = $this->sumBalances($ledger->note11Balances(2023, now()->toDateString()));
        $operationDate = now()->subDays(2)->toDateString();
        $beforeMovement = $this->sumBalances($ledger->note11Balances(
            2026,
            Carbon::parse($operationDate)->subDay()->toDateString()
        ));

        $this->actingAs($user)->post(route('fixed-assets.movements.store', $building), [
            'movement_type' => 'addition',
            'effective_date' => $operationDate,
            'amount' => '10.55',
            'description' => 'Alta adicional de prueba',
        ])->assertRedirect(route('fixed-assets.show', $building));

        $afterCutoff = $this->sumBalances($ledger->note11Balances(
            2026,
            Carbon::parse($operationDate)->subDay()->toDateString()
        ));
        $atCutoff = $this->sumBalances($ledger->note11Balances(2026, $operationDate));
        $this->assertSame($beforeMovement, $afterCutoff);
        $this->assertSame($beforeMovement + 1055, $atCutoff);

        $depreciationBeforeDisposal = $this->sumBalances($ledger->note12Balances(2026, now()->toDateString()));
        $this->actingAs($user)->patch(route('fixed-assets.dispose', $building), [
            'obsolete_disposition_status' => 'vendido',
            'effective_date' => now()->toDateString(),
            'amount' => '50.00',
        ])->assertRedirect(route('fixed-assets.show', $building));

        $depreciationAfterDisposal = $this->sumBalances($ledger->note12Balances(2026, now()->toDateString()));
        $this->assertLessThan($depreciationBeforeDisposal, $depreciationAfterDisposal);
        $this->assertSame($before2023, $this->sumBalances($ledger->note11Balances(2023, now()->toDateString())));
        $this->assertDatabaseHas('fixed_asset_movements', [
            'fixed_asset_id' => $building->id,
            'movement_type' => 'disposal',
            'amount' => '-50.00',
            'source' => 'USER_SIMULATION',
        ]);
        $this->assertDatabaseHas('fixed_asset_depreciation_entries', [
            'fixed_asset_id' => $building->id,
            'entry_type' => 'reversal',
            'source' => 'USER_SIMULATION',
        ]);
        $this->actingAs($user)
            ->get(route('fixed-assets.show', $building))
            ->assertOk()
            ->assertSee('Saldo bruto 2026 al corte')
            ->assertSee('Depreciación acumulada simulada');
        $this->actingAs($user)
            ->get(route('fixed-assets.edit', $building))
            ->assertOk()
            ->assertSee('Baja / Disposición final SICAT')
            ->assertSee('Importe bruto');
        $this->assertNotSame($land->id, $building->id);
    }

    public function test_acquisition_endpoint_records_linked_asset_movement_and_depreciation(): void
    {
        [$land, $building] = $this->createSimulationFixture();
        $this->runSimulationSeeder();
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user)
            ->get(route('fixed-assets.acquisitions.create'))
            ->assertOk()
            ->assertSee('Alta de activo fijo 2026');

        $this->actingAs($user)->post(route('fixed-assets.acquisitions.store'), [
            'internal_code' => 'INVALID-SUBCENT-ASSET',
            'name_item' => 'Importe con fracción de centavo',
            'category_id' => $building->category_id,
            'effective_date' => now()->subMonth()->toDateString(),
            'amount' => '1200.001',
        ])->assertSessionHasErrors('amount');

        $this->actingAs($user)->post(route('fixed-assets.acquisitions.store'), [
            'internal_code' => 'NEW-SIM-ASSET',
            'name_item' => 'Nuevo equipo simulado',
            'category_id' => $building->category_id,
            'effective_date' => now()->subMonth()->toDateString(),
            'amount' => '1200.00',
            'useful_life_years' => 10,
            'residual_value' => '100.00',
        ])->assertSessionHasNoErrors();

        $asset = FixedAsset::where('internal_code', 'NEW-SIM-ASSET')->firstOrFail();
        $this->assertDatabaseHas('fixed_asset_movements', [
            'fixed_asset_id' => $asset->id,
            'fixed_asset_account_id' => DB::table('fixed_asset_accounts')
                ->where('category_id', $building->category_id)
                ->where('account_type', 'fixed_asset')
                ->value('id'),
            'movement_type' => 'acquisition',
            'amount' => '1200.00',
            'source' => 'USER_SIMULATION',
        ]);
        $this->assertDatabaseHas('fixed_asset_depreciation_parameters', [
            'fixed_asset_id' => $asset->id,
            'useful_life_years' => 10,
            'residual_value' => '100.00',
            'source' => 'USER_SIMULATION',
        ]);
        $this->assertDatabaseHas('fixed_asset_depreciation_parameter_audits', [
            'depreciation_parameter_id' => DB::table('fixed_asset_depreciation_parameters')
                ->where('fixed_asset_id', $asset->id)
                ->value('id'),
            'change_type' => 'created',
            'changed_by' => $user->id,
            'source' => 'USER_SIMULATION',
        ]);
        $this->assertDatabaseHas('fixed_asset_depreciation_entries', [
            'fixed_asset_id' => $asset->id,
            'period_year' => 2026,
            'source' => 'USER_SIMULATION',
        ]);

        $summary = app(FixedAssetLedgerService::class)->dashboardSummary(now()->toDateString());
        $this->assertSame(
            $summary['gross_cents'],
            $this->sumBalances(app(FixedAssetLedgerService::class)->note11Balances(2026, now()->toDateString()))
        );
        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(number_format((float) $summary['gross'], 2, ',', '.'))
            ->assertSee(number_format((float) $summary['depreciation'], 2, ',', '.'))
            ->assertSee('Escenario SICAT simulado');
        $this->assertNotSame($land->category_id, $asset->category_id);
    }

    private function createSimulationFixture(): array
    {
        $landCategory = Category::create(['name' => 'Cuenta 23010301', 'description' => 'Terrenos simulados']);
        $buildingCategory = Category::create(['name' => 'Cuenta 24010301', 'description' => 'Edificaciones simuladas']);
        $land = FixedAsset::create([
            'internal_code' => 'SIM-LAND-01',
            'name_item' => 'Terreno de simulación',
            'category_id' => $landCategory->id,
            'quantity' => 1,
            'acquisition_value' => '1000000000.00',
        ]);
        $building = FixedAsset::create([
            'internal_code' => 'SIM-BUILDING-01',
            'name_item' => 'Edificación de simulación',
            'category_id' => $buildingCategory->id,
            'quantity' => 1,
            'acquisition_value' => '4000000000.00',
        ]);

        foreach ([
            [$land, 2023, '1000000000.00'],
            [$land, 2024, '1000000000.00'],
            [$land, 2025, '1000000000.00'],
            [$land, 2026, '1000000000.00'],
            [$building, 2023, '4000000000.00'],
            [$building, 2024, '4000000000.00'],
            [$building, 2025, '4100000000.00'],
            [$building, 2026, '4100000000.00'],
        ] as [$asset, $year, $amount]) {
            $valuationId = DB::table('fixed_asset_valuations')->insertGetId([
                'fixed_asset_id' => $asset->id,
                'valuation_year' => $year,
                'amount' => $amount,
                'as_of_date' => $year === 2026 ? '2026-01-01' : "{$year}-12-31",
                'source' => 'ORIGINAL_SPREADSHEET',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('fixed_asset_valuation_originals')->insert([
                'original_valuation_id' => $valuationId,
                'fixed_asset_id' => $asset->id,
                'valuation_year' => $year,
                'amount' => $amount,
                'as_of_date' => $year === 2026 ? '2026-01-01' : "{$year}-12-31",
                'source' => 'ORIGINAL_SPREADSHEET',
                'original_created_at' => now(),
                'original_updated_at' => now(),
                'archived_at' => now(),
                'archive_reason' => 'test fixture source archive',
            ]);
        }

        return [$land, $building];
    }

    private function runSimulationSeeder(): void
    {
        Artisan::call('db:seed', [
            '--class' => SicatFixedAssetSimulationSeeder::class,
            '--force' => true,
        ]);
    }

    private function simulationCounts(): array
    {
        return [
            DB::table('fixed_asset_accounts')->count(),
            DB::table('fixed_asset_periods')->count(),
            DB::table('fixed_asset_valuations')->count(),
            DB::table('fixed_asset_depreciation_parameters')->count(),
            DB::table('fixed_asset_depreciation_entries')->where('source', config('sicat_simulation.source'))->count(),
        ];
    }

    private function sumBalances(array $balances): int
    {
        return array_sum(array_column($balances, 'cents'));
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }
}
