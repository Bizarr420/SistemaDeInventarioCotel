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

        // Criterios iniciales de obsolescencia
        \App\Models\ObsoletenessCriterion::firstOrCreate(
            ['name' => 'Fin de soporte cercano'],
            ['type' => 'end_of_support_days', 'parameters' => ['days' => 60], 'is_active' => true]
        );

        \App\Models\ObsoletenessCriterion::firstOrCreate(
            ['name' => 'Capacidad operativa baja'],
            ['type' => 'operational_capacity_min', 'parameters' => ['min' => 60], 'is_active' => true]
        );

        \App\Models\ObsoletenessCriterion::firstOrCreate(
            ['name' => 'Vida útil insuficiente'],
            ['type' => 'useful_life_below', 'parameters' => ['limit' => 70], 'is_active' => true]
        );
    }
}
