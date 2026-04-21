<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\ObsoletenessCriterion;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductObsolescenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_obsolete_if_end_of_support_passed()
    {
        $product = Product::create([
            'name_item' => 'Equipo antigua',
            'end_of_support' => now()->subDay(),
        ]);

        $this->assertTrue($product->isObsolete());
    }

    public function test_is_obsolete_if_incompatible_status()
    {
        $product = Product::create([
            'name_item' => 'Equipo no compatible',
            'compatibility_status' => 'incompatible',
        ]);

        $this->assertTrue($product->isObsolete());
    }

    public function test_is_obsolete_if_operational_capacity_low()
    {
        $product = Product::create([
            'name_item' => 'Equipo defectuoso',
            'operational_capacity' => 30,
        ]);

        $this->assertTrue($product->isObsolete());
    }

    public function test_is_obsolete_by_active_criterion_match()
    {
        $product = Product::create([
            'name_item' => 'Equipo cercano a fin de soporte',
            'end_of_support' => now()->addDays(5),
            'operational_capacity' => 80,
        ]);

        ObsoletenessCriterion::create([
            'name' => 'soporte cercano',
            'type' => 'end_of_support_days',
            'parameters' => ['days' => 10],
            'is_active' => true,
        ]);

        $this->assertTrue($product->isObsolete());
    }

    public function test_useful_life_and_deterioration_and_patrimonial_deviation_calculations()
    {
        $product = Product::create([
            'name_item' => 'Equipo calculo',
            'acquisition_date' => now()->subYears(3),
            'useful_life_years' => 10,
            'current_accounting_value' => 800.00,
            'technical_value' => 1200.00,
        ]);

        $usefulLife = $product->calculateUsefulLife();
        $deterioration = $product->calculateDeterioration();
        $deviation = $product->calculatePatrimonialDeviation();

        $this->assertGreaterThanOrEqual(0, $usefulLife);
        $this->assertLessThanOrEqual(100, $usefulLife);
        $this->assertEquals(100.0 - $usefulLife, $deterioration);
        $this->assertSame(400.0, $deviation);
    }
}
