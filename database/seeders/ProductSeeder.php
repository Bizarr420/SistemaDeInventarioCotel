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

        for ($i = 1; $i <= 100; $i++) {
            Product::create([
                'internal_code' => 'INT-' . Str::padLeft($i, 4, '0'),
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
            ]);
        }
    }
}
