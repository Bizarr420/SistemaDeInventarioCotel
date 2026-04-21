<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Str;
class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Category::firstOrCreate(['id' => 1], ['name' => 'General']);
        \App\Models\Supplier::firstOrCreate(['id' => 1], ['name' => 'Genérico']);

        for ($i = 1; $i <= 20; $i++) {
            $code = 'INT-' . Str::padLeft($i, 4, '0');

            Product::updateOrCreate(
                ['internal_code' => $code],
                [
                    'part_number'   => 'PN-' . Str::padLeft($i, 4, '0'),
                    'item'          => 'Item ' . $i,
                    'name_item'     => 'Producto de prueba ' . $i,
                    'cnd'           => 'CND-' . rand(100, 999),
                    'unit'          => 'UND',
                    'mac'           => strtoupper(Str::random(12)),
                    'description'   => 'Descripción del producto ' . $i,
                    'note'          => 'Observación del producto ' . $i,
                    'category_id'   => 1,
                    'supplier_id'   => 1,
                    'acquisition_date' => now()->subYears(rand(1, 10)),
                    'useful_life_years' => rand(3, 15),
                    'current_accounting_value' => rand(500, 4000),
                    'technical_value' => rand(600, 4500),
                    'operational_capacity' => rand(30, 100),
                    'end_of_support' => now()->addDays(rand(-120, 180)),
                    'compatibility_status' => rand(0, 1) ? 'compatible' : 'incompatible',
                ]
            );
        }

        // Casos específicos para activar criterios de obsolescencia
        Product::updateOrCreate([
            'internal_code' => 'INT-0001'
        ],[
            'part_number'   => 'PN-0001',
            'item'          => 'Servidor obsoleto SOP',
            'name_item'     => 'Servidor fin soporte',
            'category_id'   => 1,
            'supplier_id'   => 1,
            'acquisition_date' => now()->subYears(8),
            'useful_life_years' => 10,
            'current_accounting_value' => 2500,
            'technical_value' => 1800,
            'operational_capacity' => 90,
            'end_of_support' => now()->addDays(30),
            'compatibility_status' => 'compatible',
        ]);

        Product::updateOrCreate([
            'internal_code' => 'INT-0002'
        ],[
            'part_number'   => 'PN-0002',
            'item'          => 'Equipo capacidad baja',
            'name_item'     => 'Equipo opcap baja',
            'category_id'   => 1,
            'supplier_id'   => 1,
            'acquisition_date' => now()->subYears(5),
            'useful_life_years' => 12,
            'current_accounting_value' => 1500,
            'technical_value' => 1400,
            'operational_capacity' => 45,
            'end_of_support' => now()->addDays(120),
            'compatibility_status' => 'compatible',
        ]);

        Product::updateOrCreate([
            'internal_code' => 'INT-0003'
        ],[
            'part_number'   => 'PN-0003',
            'item'          => 'Equipo vida util baja',
            'name_item'     => 'Equipo life under limit',
            'category_id'   => 1,
            'supplier_id'   => 1,
            'acquisition_date' => now()->subYears(8),
            'useful_life_years' => 10,
            'current_accounting_value' => 2300,
            'technical_value' => 2100,
            'operational_capacity' => 82,
            'end_of_support' => now()->addDays(360),
            'compatibility_status' => 'compatible',
        ]);
    }
}
