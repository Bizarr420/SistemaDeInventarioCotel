<?php

namespace Database\Seeders;

use App\Models\InventoryBalanceSnapshot;
use Illuminate\Database\Seeder;

class InventoryBalanceSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        $lines = [
            ['17010101', 'Papelería y Útiles de Oficina', 60333.61, 71381.49],
            ['17010301', 'Materiales Repuestos y Suministros', 7775750.06, 8344775.86],
            ['17010401', 'Combustibles Lubricantes', 200.20, 208.51],
            ['17010501', 'Materiales de Seguridad (Equipo de Trabajo)', 12568.08, 13167.80],
            ['17020501', 'Otros inventarios', 8008663.03, 8435438.65],
            ['17030101', 'Provisión para obsolescencia inventarios', -2312368.77, -2408346.91],
            ['17039999', 'Ajuste de conciliación de saldos', 0.00, 0.10],
        ];

        foreach ($lines as [$code, $detail, $value2024, $value2023]) {
            $variation = $value2024 - $value2023;

            foreach ([
                2023 => [$value2023, false],
                2024 => [$value2024, false],
                2025 => [$value2024 + $variation, true],
                2026 => [$value2024 + (2 * $variation), true],
            ] as $year => [$amount, $isEstimated]) {
                InventoryBalanceSnapshot::updateOrCreate(
                    ['balance_year' => $year, 'account_code' => $code],
                    [
                        'detail' => $detail,
                        'amount' => $amount,
                        'is_estimated' => $isEstimated,
                        'source' => 'Nota 9 - conciliacion con inventario existente',
                    ]
                );
            }
        }
    }
}
