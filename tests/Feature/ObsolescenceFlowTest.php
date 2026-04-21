<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ObsoletenessCriterion;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObsolescenceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_obsolescence_detection_flow_with_criteria_creation()
    {
        $category = Category::create(['name' => 'Hardware', 'description' => 'Equipos']);
        $supplier = Supplier::create(['name' => 'Proveedor X', 'contact' => 'Admin', 'phone' => '123456', 'email' => 'x@x.com']);

        $product = Product::create([
            'name_item' => 'Servidor A',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'end_of_support' => now()->addDays(40),
            'operational_capacity' => 90,
            'acquisition_date' => now()->subYears(6),
            'useful_life_years' => 10,
        ]);

        ObsoletenessCriterion::create([
            'name' => 'relleno vida util',
            'type' => 'useful_life_below',
            'parameters' => ['limit' => 70],
            'is_active' => true,
        ]);

        $this->assertTrue($product->isObsolete(), 'El producto debe ser tratado como obsoleto mediante criterio de vida útil.');
    }
}
