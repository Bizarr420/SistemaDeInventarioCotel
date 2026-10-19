<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $filePath = __DIR__ . '/Planilla_Maestra_50_Items_Todas_Las_Cuentas.xlsx';
    
    echo "Verificando archivo completo...\n\n";
    
    $zip = new ZipArchive;
    if ($zip->open($filePath) !== TRUE) {
        echo "No se pudo abrir el archivo como ZIP\n";
        exit(1);
    }

    // Leer shared strings
    $sharedStrings = [];
    $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedStringsXml) {
        $xml = simplexml_load_string($sharedStringsXml);
        foreach ($xml->si as $si) {
            $sharedStrings[] = (string) $si->t;
        }
    }

    // Leer worksheet
    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $xml = simplexml_load_string($sheetXml);
    $rows = $xml->sheetData->row;

    echo "Total de filas XML: " . count($rows) . "\n\n";

    // Contar todos los patrones de activos
    $rowNumber = 0;
    $categories = [];
    $currentCategory = null;
    $uniqueCategories = [];
    $totalAssets = 0;

    foreach ($rows as $rowIndex => $row) {
        $rowNumber++;
        
        if (!isset($row->c)) {
            continue;
        }

        $rowData = [];
        $cells = $row->c;

        foreach ($cells as $cell) {
            $attributes = $cell->attributes();
            $colRef = (string) $attributes['r'];
            
            $value = '';
            if (isset($cell->v)) {
                $cellValue = (string) $cell->v;
                if (isset($attributes['t']) && (string) $attributes['t'] === 's') {
                    $value = $sharedStrings[(int) $cellValue] ?? '';
                } else {
                    $value = $cellValue;
                }
            }
            $rowData[$colRef] = $value;
        }

        // Buscar código de cuenta (8 dígitos) en cualquier columna
        $accountCode = null;
        foreach ($rowData as $col => $val) {
            if (preg_match('/^\d{8}$/', $val)) {
                $accountCode = $val;
                if (!isset($uniqueCategories[$accountCode])) {
                    $uniqueCategories[$accountCode] = [
                        'code' => $accountCode,
                        'count' => 0
                    ];
                }
                $currentCategory = $accountCode;
                break;
            }
        }

        // Buscar código de activo (XXX-###) en cualquier columna
        $assetCode = null;
        foreach ($rowData as $col => $val) {
            if (preg_match('/^[A-Z]{2,4}-\d{3}$/', $val)) {
                $assetCode = $val;
                if ($currentCategory && isset($uniqueCategories[$currentCategory])) {
                    $uniqueCategories[$currentCategory]['count']++;
                }
                $totalAssets++;
                break;
            }
        }
    }

    $zip->close();

    echo "=== RESULTADO ===\n";
    echo "Categorías únicas: " . count($uniqueCategories) . "\n";
    echo "Total de activos: $totalAssets\n\n";
    
    echo "Detalle por categoría:\n";
    foreach ($uniqueCategories as $cat) {
        echo "  {$cat['code']}: {$cat['count']} activos\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
