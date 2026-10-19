<?php

return [
    'source' => 'SICAT_SIMULATION_V1',
    'note_11_references' => [
        2023 => '3694912310.46',
        2024 => '3693575694.68',
    ],
    'note_12_references' => [
        2023 => '3311769226.93',
        2024 => '3347599253.20',
    ],
    'cutoff_date_2026' => env('SICAT_SIMULATION_CUTOFF_DATE', date('Y-m-d')),
    'asset_classes' => [
        '23010301' => ['method' => 'none', 'useful_life_years' => null, 'residual_rate' => 0, 'profile' => 'Terrenos; no depreciable en escenario'],
        '24010301' => ['method' => 'straight_line', 'useful_life_years' => [38, 40, 42], 'residual_rate' => 0.05, 'profile' => 'Edificaciones'],
        'default' => ['method' => 'straight_line', 'useful_life_years' => [25, 30, 35], 'residual_rate' => 0.05, 'profile' => 'Equipos y redes; vida útil simulada por activo'],
    ],
    'periods' => [
        2023 => 'closed',
        2024 => 'closed',
        2025 => 'closed',
        2026 => 'open',
    ],
];
