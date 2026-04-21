<?php

namespace Database\Seeders;

use App\Models\AssetVerification;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FixedAssetSampleSeeder extends Seeder
{
    public function run(): void
    {
        $categoryComputers = Category::updateOrCreate(
            ['name' => 'Computadoras'],
            ['description' => 'Equipos de computo en uso institucional']
        );

        $categoryPrinters = Category::updateOrCreate(
            ['name' => 'Impresoras'],
            ['description' => 'Equipos de impresion y multifuncionales']
        );

        $categoryServers = Category::updateOrCreate(
            ['name' => 'Servidores'],
            ['description' => 'Servidores de red y procesamiento']
        );

        $categoryNetwork = Category::updateOrCreate(
            ['name' => 'Equipos de Red'],
            ['description' => 'Switches, routers y elementos de conectividad']
        );

        $supplierA = Supplier::updateOrCreate(
            ['name' => 'TecnoBol S.R.L.'],
            ['contact' => 'Carlos Rojas', 'phone' => '70011111', 'email' => 'ventas@tecnobol.bo']
        );

        $supplierB = Supplier::updateOrCreate(
            ['name' => 'InfraRed Bolivia'],
            ['contact' => 'Laura Perez', 'phone' => '70022222', 'email' => 'contacto@infrared.bo']
        );

        $supplierC = Supplier::updateOrCreate(
            ['name' => 'DataCenter Andino'],
            ['contact' => 'Miguel Sanchez', 'phone' => '70033333', 'email' => 'soporte@datacenterandino.bo']
        );

        Warehouse::updateOrCreate(
            ['code' => 'AF-01'],
            ['name' => 'Almacen Activos Fijos', 'location' => 'Edificio Central']
        );

        $assets = [];

        // 20 computadoras adquiridas en 2024 con vida util de 10 anios.
        for ($i = 1; $i <= 20; $i++) {
            $month = (($i - 1) % 12) + 1;
            $day = min(28, 5 + $i);
            $assets[] = [
                'internal_code' => sprintf('AF-PC-%03d', $i),
                'part_number' => sprintf('PC-2024-%03d', $i),
                'item' => 'Computadora',
                'name_item' => sprintf('Computadora de Oficina %02d', $i),
                'category_id' => $categoryComputers->id,
                'supplier_id' => $supplierA->id,
                'warehouse_id' => null,
                'location_branch' => $i <= 10 ? 'Sucursal Central' : 'Sucursal Norte',
                'location_floor' => $i <= 10 ? '2' : '1',
                'location_office' => $i <= 10 ? 'Oficina Sistemas' : 'Oficina Comercial',
                'assigned_to' => $i <= 10 ? 'Equipo TI' : 'Usuario Operativo '.$i,
                'assigned_department' => $i <= 10 ? 'Tecnologia' : 'Comercial',
                'quantity' => 1,
                'unit_cost' => 650.00,
                'sku' => sprintf('SKU-AF-PC-%03d', $i),
                'description' => 'Computadora de escritorio adquirida en 2024.',
                'note' => 'Activo fijo institucional',
                'unit' => 'UND',
                'type' => 'asset',
                'useful_life_years' => 10,
                'acquisition_date' => Carbon::create(2024, $month, $day)->toDateString(),
                'acquisition_value' => 650.00,
                'current_accounting_value' => 650.00,
                'technical_value' => 650.00,
                'obsolescence_status' => 'active',
            ];
        }

        // Impresoras 2024
        for ($i = 1; $i <= 6; $i++) {
            $assets[] = [
                'internal_code' => sprintf('AF-IMP-%03d', $i),
                'part_number' => sprintf('IMP-2024-%03d', $i),
                'item' => 'Impresora',
                'name_item' => sprintf('Impresora Laser %02d', $i),
                'category_id' => $categoryPrinters->id,
                'supplier_id' => $supplierB->id,
                'warehouse_id' => null,
                'location_branch' => 'Sucursal Central',
                'location_floor' => (string) (($i % 3) + 1),
                'location_office' => 'Area Administrativa',
                'assigned_to' => 'Encargado de Oficina '.$i,
                'assigned_department' => 'Administracion',
                'quantity' => 1,
                'unit_cost' => 320.00,
                'sku' => sprintf('SKU-AF-IMP-%03d', $i),
                'description' => 'Impresora laser de red adquirida en 2024.',
                'note' => 'Activo fijo institucional',
                'unit' => 'UND',
                'type' => 'asset',
                'useful_life_years' => 5,
                'acquisition_date' => Carbon::create(2024, 6, min(28, 8 + $i))->toDateString(),
                'acquisition_value' => 320.00,
                'current_accounting_value' => 320.00,
                'technical_value' => 320.00,
                'obsolescence_status' => 'active',
            ];
        }

        // Servidores de red 2024
        for ($i = 1; $i <= 4; $i++) {
            $assets[] = [
                'internal_code' => sprintf('AF-SRV-%03d', $i),
                'part_number' => sprintf('SRV-2024-%03d', $i),
                'item' => 'Servidor',
                'name_item' => sprintf('Servidor de Red %02d', $i),
                'category_id' => $categoryServers->id,
                'supplier_id' => $supplierC->id,
                'warehouse_id' => null,
                'location_branch' => 'Sucursal Central',
                'location_floor' => 'PB',
                'location_office' => 'Cuarto de Servidores',
                'assigned_to' => 'Administrador de Infraestructura',
                'assigned_department' => 'Tecnologia',
                'quantity' => 1,
                'unit_cost' => 2600.00,
                'sku' => sprintf('SKU-AF-SRV-%03d', $i),
                'description' => 'Servidor de red para servicios internos.',
                'note' => 'Activo fijo critico',
                'unit' => 'UND',
                'type' => 'asset',
                'useful_life_years' => 8,
                'acquisition_date' => Carbon::create(2024, 3, min(28, 10 + $i))->toDateString(),
                'acquisition_value' => 2600.00,
                'current_accounting_value' => 2600.00,
                'technical_value' => 2600.00,
                'obsolescence_status' => 'active',
            ];
        }

        // Switches de red 2024
        for ($i = 1; $i <= 5; $i++) {
            $assets[] = [
                'internal_code' => sprintf('AF-SW-%03d', $i),
                'part_number' => sprintf('SW-2024-%03d', $i),
                'item' => 'Switch',
                'name_item' => sprintf('Switch de Acceso %02d', $i),
                'category_id' => $categoryNetwork->id,
                'supplier_id' => $supplierB->id,
                'warehouse_id' => null,
                'location_branch' => $i <= 3 ? 'Sucursal Central' : 'Sucursal Sur',
                'location_floor' => $i <= 3 ? 'PB' : '1',
                'location_office' => 'Rack de Comunicaciones',
                'assigned_to' => 'Tecnico de Redes',
                'assigned_department' => 'Tecnologia',
                'quantity' => 1,
                'unit_cost' => 540.00,
                'sku' => sprintf('SKU-AF-SW-%03d', $i),
                'description' => 'Switch de red capa 2.',
                'note' => 'Activo de comunicaciones',
                'unit' => 'UND',
                'type' => 'asset',
                'useful_life_years' => 4,
                'acquisition_date' => Carbon::create(2024, 2, min(28, 12 + $i))->toDateString(),
                'acquisition_value' => 540.00,
                'current_accounting_value' => 540.00,
                'technical_value' => 540.00,
                'obsolescence_status' => 'active',
            ];
        }

        foreach ($assets as $assetData) {
            $product = Product::updateOrCreate(
                ['internal_code' => $assetData['internal_code']],
                $assetData
            );

            $acquisitionDate = Carbon::parse($product->acquisition_date);
            $years = max(1, (int) ($product->useful_life_years ?? 1));
            $elapsedYears = $acquisitionDate->floatDiffInYears(now());

            // Regla solicitada por usuario: 100% al comprar, 0% al cumplir la vida util.
            $remainingPercent = (int) max(0, min(100, round(100 - (($elapsedYears / $years) * 100))));

            $status = 'operativo';
            if ($remainingPercent < 20) {
                $status = 'obsoleto';
            } elseif ($remainingPercent < 40) {
                $status = 'falla';
            } elseif ($remainingPercent < 70) {
                $status = 'deteriorado';
            }

            $product->update([
                'obsolescence_status' => $status === 'obsoleto' ? 'obsolete' : 'active',
            ]);

            AssetVerification::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'verified_at' => now()->toDateString(),
                ],
                [
                    'verified_by' => 1,
                    'status' => $status,
                    'deterioration_level' => $remainingPercent,
                    'notes' => 'Verificacion automatica inicial basada en fecha de compra y vida util.',
                    'next_verification_at' => now()->addMonths(6)->toDateString(),
                ]
            );
        }
    }
}
