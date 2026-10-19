<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\FixedAsset;
use App\Models\Warehouse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportFixedAssetsFromExcel extends Command
{
    protected $signature = 'fixed-assets:import {file} {--force : Forzar importación sin confirmación}';
    protected $description = 'Importar activos fijos desde archivo Excel';

    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("Archivo no encontrado: $filePath");
            return 1;
        }

        $this->info("Leyendo archivo Excel: $filePath");

        try {
            $zip = new \ZipArchive;
            if ($zip->open($filePath) !== TRUE) {
                $this->error("No se pudo abrir el archivo como ZIP");
                return 1;
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

            $this->info("Total de filas en el archivo: " . count($rows));

            // Procesar filas
            $allAssets = [];
            $currentCategory = null;
            $rowNumber = 0;

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
                $accountName = null;
                foreach ($rowData as $col => $val) {
                    if (preg_match('/^\d{8}$/', $val)) {
                        $accountCode = $val;
                        // Buscar el nombre en la siguiente columna de la misma fila
                        $colLetter = preg_replace('/\d+$/', '', $col);
                        $nextCol = $colLetter . ($rowNumber);
                        $accountName = $rowData[$nextCol] ?? $val;
                        break;
                    }
                }
                
                if ($accountCode) {
                    $currentCategory = [
                        'code' => $accountCode,
                        'name' => $accountName,
                        'assets' => []
                    ];
                    $this->info("Categoría: $accountCode - " . substr($accountName, 0, 50));
                    continue;
                }

                // Buscar código de activo (XXX-###) en cualquier columna
                $assetCode = null;
                $assetName = null;
                $colC = null;
                $colD = null;
                $colE = null;
                $colF = null;
                
                foreach ($rowData as $col => $val) {
                    if (preg_match('/^[A-Z]{2,4}-\d{3}$/', $val)) {
                        $assetCode = $val;
                        // Buscar el nombre en la siguiente columna
                        $colLetter = preg_replace('/\d+$/', '', $col);
                        $nextCol = $colLetter . ($rowNumber);
                        $assetName = $rowData[$nextCol] ?? '';
                        
                        // Buscar valores en columnas C, D, E, F de esta fila
                        $colC = $rowData['C' . $rowNumber] ?? '';
                        $colD = $rowData['D' . $rowNumber] ?? '';
                        $colE = $rowData['E' . $rowNumber] ?? '';
                        $colF = $rowData['F' . $rowNumber] ?? '';
                        break;
                    }
                }

                if ($assetCode && $currentCategory) {
                    $allAssets[] = [
                        'internal_code' => $assetCode,
                        'name_item' => $assetName,
                        'account_code' => $currentCategory['code'],
                        'account_name' => $currentCategory['name'],
                        'value_2023' => is_numeric($colC) ? (float) $colC : 0,
                        'value_2024' => is_numeric($colD) ? (float) $colD : 0,
                        'value_2025' => is_numeric($colE) ? (float) $colE : 0,
                        'value_2026' => is_numeric($colF) ? (float) $colF : 0,
                    ];
                }
            }

            $zip->close();

            $this->info("Activos detectados: " . count($allAssets));

            if (count($allAssets) === 0) {
                $this->error("No se encontraron activos para importar");
                return 1;
            }

            // Confirmar eliminación
            if (!$this->option('force') && !$this->confirm("¿Está seguro de eliminar TODOS los activos fijos existentes e importar " . count($allAssets) . " nuevos activos?")) {
                $this->info("Importación cancelada");
                return 0;
            }

            // Eliminar activos existentes
            $this->info("Eliminando activos fijos existentes...");
            FixedAsset::query()->delete();
            $this->info("Activos eliminados");

            // Obtener o crear almacén por defecto
            $warehouse = Warehouse::firstOrCreate(
                ['code' => 'ALM-001'],
                ['name' => 'Almacén Principal', 'location' => 'Sede Principal']
            );

            // Agrupar activos por cuenta contable para crear categorías
            $categoriesMap = [];
            foreach ($allAssets as $asset) {
                $accountCode = $asset['account_code'];
                if (!isset($categoriesMap[$accountCode])) {
                    $category = Category::firstOrCreate(
                        ['name' => 'Cuenta ' . $accountCode],
                        ['description' => substr($asset['account_name'], 0, 255)]
                    );
                    $categoriesMap[$accountCode] = $category;
                }
            }

            // Importar activos
            $this->info("Importando activos...");
            $bar = $this->output->createProgressBar(count($allAssets));
            $bar->start();

            DB::beginTransaction();
            try {
                foreach ($allAssets as $assetData) {
                    $category = $categoriesMap[$assetData['account_code']];
                    
                    FixedAsset::create([
                        'internal_code' => $assetData['internal_code'],
                        'name_item' => $assetData['name_item'],
                        'description' => "Cuenta contable: " . $assetData['account_code'],
                        'category_id' => $category->id,
                        'warehouse_id' => $warehouse->id,
                        'acquisition_value' => $assetData['value_2026'], // Usar valor más reciente
                        'current_accounting_value' => $assetData['value_2026'],
                        'technical_value' => $assetData['value_2026'],
                        'asset_status' => 'operativo',
                        'quantity' => 1,
                        'unit_cost' => $assetData['value_2026'],
                        'acquisition_date' => now()->subYears(3), // Estimado: hace 3 años
                        'useful_life_years' => 10,
                        'expected_useful_life' => now()->addYears(7),
                    ]);

                    $bar->advance();
                }

                DB::commit();
                $bar->finish();
                $this->newLine();
                $this->info("Importación completada exitosamente: " . count($allAssets) . " activos importados");
                $this->info("Categorías creadas: " . count($categoriesMap));

                return 0;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Error durante la importación: " . $e->getMessage());
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
