<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BasicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Category::firstOrCreate(['name'=>'General']);
        \App\Models\Supplier::firstOrCreate(['name'=>'Genérico']);
        // database/seeders/BasicSeeder.php (agrega)
        \App\Models\Warehouse::firstOrCreate(['code'=>'ALM-01'], ['name'=>'Almacén Central']);
        \App\Models\Warehouse::firstOrCreate(['code'=>'ALM-02'], ['name'=>'Almacén Secundario']);

    }
}
