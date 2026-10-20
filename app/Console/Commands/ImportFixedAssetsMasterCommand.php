<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Services\AuditEventService;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class ImportFixedAssetsMasterCommand extends Command
{
    protected $signature = 'fixed-assets:import-master
        {path? : Path to the master XLSX file}
        {--replace : Replace all fixed assets already in the database}
        {--force : Skip the replacement confirmation}';

    protected $description = 'Replace fixed assets with the master spreadsheet and import annual valuations';

    private const YEARS = [2023, 2024, 2025, 2026];

    private const ITEMS_PER_CATEGORY = 50;

    private const EXPECTED_CATEGORIES = 35;

    public function handle(AuditEventService $audit): int
    {
        if (! $this->option('replace')) {
            $this->error('No data changed. Pass --replace to replace the current fixed assets.');

            return self::FAILURE;
        }

        if ($this->hasLedgerHistory()) {
            $this->error('Import refused: archived snapshots or ledger records exist. Do not replace assets with financial history.');

            return self::FAILURE;
        }

        $path = $this->argument('path')
            ?: base_path('Planilla_Maestra_50_Items_Todas_Las_Cuentas.xlsx');

        if (! is_file($path)) {
            $this->error("Spreadsheet not found: {$path}");

            return self::FAILURE;
        }

        try {
            $data = $this->readSpreadsheet($path);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $existingCount = DB::table('fixed_assets')->count();
        $this->warn(sprintf(
            'This will replace %d fixed assets (and delete linked verifications, opinions, and adjustments) with %d assets in %d account categories.',
            $existingCount,
            count($data['assets']),
            count($data['categories'])
        ));

        if (! $this->option('force') && ! $this->confirm('Continue with the replacement?')) {
            $this->info('Import cancelled. No data changed.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($data, $audit): void {
            DB::table('fixed_asset_valuations')->delete();
            DB::table('fixed_assets')->delete();

            $now = now();
            $categoryRows = [];
            foreach ($data['categories'] as $accountCode => $category) {
                $categoryRows[] = [
                    'name' => 'Cuenta '.$accountCode,
                    'description' => $category['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('categories')->upsert(
                $categoryRows,
                ['name'],
                ['description', 'updated_at']
            );

            $categoryIds = DB::table('categories')
                ->whereIn('name', array_map(
                    fn (string $accountCode): string => 'Cuenta '.$accountCode,
                    array_keys($data['categories'])
                ))
                ->pluck('id', 'name')
                ->all();

            $assetRows = [];
            foreach ($data['assets'] as $asset) {
                $categoryName = 'Cuenta '.$asset['account_code'];
                $yearValues = $asset['values'];
                $assetRows[] = [
                    'internal_code' => $asset['code'],
                    'name_item' => $asset['name'],
                    'description' => $asset['name'],
                    'note' => 'Planilla maestra; valores expresados en Bs.',
                    'category_id' => $categoryIds[$categoryName],
                    'unit' => 'unidad',
                    'quantity' => 1,
                    'unit_cost' => $yearValues[2026],
                    'acquisition_value' => $yearValues[2023],
                    'current_accounting_value' => $yearValues[2026],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($assetRows, 250) as $chunk) {
                DB::table('fixed_assets')->insert($chunk);
            }

            $assetIds = DB::table('fixed_assets')
                ->get(['id', 'internal_code'])
                ->pluck('id', 'internal_code')
                ->all();

            $valuationRows = [];
            foreach ($data['assets'] as $asset) {
                foreach ($asset['values'] as $year => $amount) {
                    $valuationRows[] = [
                        'fixed_asset_id' => $assetIds[$asset['code']],
                        'valuation_year' => $year,
                        'amount' => $amount,
                        'as_of_date' => null,
                        'source' => 'Planilla_Maestra_50_Items_Todas_Las_Cuentas.xlsx (simulación)',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            foreach (array_chunk($valuationRows, 500) as $chunk) {
                DB::table('fixed_asset_valuations')->insert($chunk);
            }
            $audit->record('fixed-assets', 'master_import', null,
                'Importación de planilla maestra de activos completada.',
                newValues: [
                    'assets_imported' => count($data['assets']),
                    'categories_imported' => count($data['categories']),
                    'valuations_imported' => count($data['assets']) * count(self::YEARS),
                    'actor' => 'system',
                ],
                context: ['source_file' => basename($this->argument('path') ?: 'Planilla_Maestra_50_Items_Todas_Las_Cuentas.xlsx')]);
        });

        $this->info(sprintf(
            'Imported %d fixed assets, %d categories, and %d annual valuations.',
            count($data['assets']),
            count($data['categories']),
            count($data['assets']) * count(self::YEARS)
        ));

        return self::SUCCESS;
    }

    /**
     * @return array{
     *     categories: array<string, array{description: string}>,
     *     assets: array<int, array{account_code: string, code: string, name: string, values: array<int, string>}>
     * }
     */
    private function readSpreadsheet(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException("Unable to open spreadsheet: {$path}");
        }

        try {
            $sharedStrings = $this->readSharedStrings($zip);
            $sheetContents = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($sheetContents === false) {
                throw new RuntimeException('The spreadsheet does not contain its first worksheet.');
            }

            $sheet = simplexml_load_string($sheetContents, SimpleXMLElement::class, LIBXML_NONET);
            if ($sheet === false) {
                throw new RuntimeException('The first worksheet contains invalid XML.');
            }

            $worksheetRows = $sheet->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]');
            $rows = array_map(
                fn (SimpleXMLElement $row): array => $this->readWorksheetRow($row, $sharedStrings),
                $worksheetRows ?: []
            );
        } finally {
            $zip->close();
        }

        $categories = [];
        $assets = [];
        $codes = [];
        $currentAccount = null;
        $itemsInCurrentAccount = 0;

        foreach ($rows as $row) {
            $code = trim((string) ($row[0] ?? ''));
            $detail = trim((string) ($row[1] ?? ''));

            if (preg_match('/^\d{8}$/', $code) && Str::startsWith($detail, 'CUENTA:')) {
                if ($currentAccount !== null) {
                    throw new RuntimeException("Account {$currentAccount} is missing its subtotal row.");
                }

                if (isset($categories[$code])) {
                    throw new RuntimeException("Duplicate account code {$code} in the spreadsheet.");
                }

                $description = preg_replace('/^CUENTA:\s*/u', '', $detail);
                $description = preg_replace('/\s*\(SUMATORIA.*$/u', '', $description);
                $categories[$code] = ['description' => trim($description)];
                $currentAccount = $code;
                $itemsInCurrentAccount = 0;

                continue;
            }

            if ($currentAccount === null) {
                continue;
            }

            if ($code === 'SUBTOTAL') {
                if ($itemsInCurrentAccount !== self::ITEMS_PER_CATEGORY) {
                    throw new RuntimeException(sprintf(
                        'Account %s has %d assets; expected %d.',
                        $currentAccount,
                        $itemsInCurrentAccount,
                        self::ITEMS_PER_CATEGORY
                    ));
                }

                $this->readYearValues($row, $currentAccount);
                $currentAccount = null;

                continue;
            }

            if ($code === '' && $detail === '') {
                continue;
            }

            if ($code === '' || $detail === '') {
                throw new RuntimeException("Incomplete asset row in account {$currentAccount}.");
            }

            if (isset($codes[$code])) {
                throw new RuntimeException("Duplicate asset code {$code} in the spreadsheet.");
            }

            $name = trim((string) preg_replace('/^└─\s*/u', '', $detail));
            if ($name === '' || Str::length($name) > 150) {
                throw new RuntimeException("Asset {$code} has an empty name or a name longer than 150 characters.");
            }

            $codes[$code] = true;
            $assets[] = [
                'account_code' => $currentAccount,
                'code' => $code,
                'name' => $name,
                'values' => $this->readYearValues($row, $code),
            ];
            $itemsInCurrentAccount++;
        }

        if ($currentAccount !== null) {
            throw new RuntimeException("Account {$currentAccount} is missing its subtotal row.");
        }

        if (count($categories) !== self::EXPECTED_CATEGORIES) {
            throw new RuntimeException(sprintf(
                'The spreadsheet has %d account categories; expected %d.',
                count($categories),
                self::EXPECTED_CATEGORIES
            ));
        }

        if (count($assets) !== self::EXPECTED_CATEGORIES * self::ITEMS_PER_CATEGORY) {
            throw new RuntimeException(sprintf(
                'The spreadsheet has %d assets; expected %d.',
                count($assets),
                self::EXPECTED_CATEGORIES * self::ITEMS_PER_CATEGORY
            ));
        }

        return compact('categories', 'assets');
    }

    private function hasLedgerHistory(): bool
    {
        foreach ([
            'fixed_asset_valuation_originals',
            'fixed_asset_profile_originals',
            'fixed_asset_movements',
            'fixed_asset_depreciation_entries',
            'fixed_asset_depreciation_parameters',
        ] as $table) {
            if (Schema::hasTable($table) && DB::table($table)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function readSharedStrings(ZipArchive $zip): array
    {
        $contents = $zip->getFromName('xl/sharedStrings.xml');
        if ($contents === false) {
            return [];
        }

        $xml = simplexml_load_string($contents, SimpleXMLElement::class, LIBXML_NONET);
        if ($xml === false) {
            throw new RuntimeException('The spreadsheet contains invalid shared strings XML.');
        }

        $strings = [];
        foreach ($xml->xpath('//*[local-name()="si"]') ?: [] as $stringItem) {
            $textParts = $stringItem->xpath('.//*[local-name()="t"]') ?: [];
            $strings[] = implode('', array_map(
                static fn (SimpleXMLElement $text): string => (string) $text,
                $textParts
            ));
        }

        return $strings;
    }

    /**
     * @param  array<int, string>  $sharedStrings
     * @return array<int, string|null>
     */
    private function readWorksheetRow(SimpleXMLElement $row, array $sharedStrings): array
    {
        $values = array_fill(0, 6, null);
        foreach ($row->xpath('./*[local-name()="c"]') ?: [] as $cell) {
            $attributes = $cell->attributes();
            if (! preg_match('/^([A-Z]+)/', (string) ($attributes['r'] ?? ''), $matches)) {
                continue;
            }

            $columnIndex = 0;
            foreach (str_split($matches[1]) as $letter) {
                $columnIndex = ($columnIndex * 26) + ord($letter) - ord('A') + 1;
            }
            if ($columnIndex > count($values)) {
                continue;
            }

            $type = (string) ($attributes['t'] ?? '');
            if ($type === 's') {
                $valueNodes = $cell->xpath('./*[local-name()="v"]');
                $sharedStringIndex = isset($valueNodes[0]) ? (int) $valueNodes[0] : -1;
                if (! array_key_exists($sharedStringIndex, $sharedStrings)) {
                    throw new RuntimeException('The spreadsheet refers to a missing shared string.');
                }

                $values[$columnIndex - 1] = $sharedStrings[$sharedStringIndex];

                continue;
            }

            if ($type === 'inlineStr') {
                $textParts = $cell->xpath('./*[local-name()="is"]//*[local-name()="t"]') ?: [];
                $values[$columnIndex - 1] = implode('', array_map(
                    static fn (SimpleXMLElement $text): string => (string) $text,
                    $textParts
                ));

                continue;
            }

            $valueNodes = $cell->xpath('./*[local-name()="v"]');
            $values[$columnIndex - 1] = isset($valueNodes[0]) ? (string) $valueNodes[0] : null;
        }

        return $values;
    }

    /**
     * @param  array<int, mixed>  $row
     * @return array<int, string>
     */
    private function readYearValues(array $row, string $rowLabel): array
    {
        $values = [];
        foreach (self::YEARS as $offset => $year) {
            $value = $row[$offset + 2] ?? null;
            if (! is_numeric($value) || (float) $value < 0) {
                throw new RuntimeException("Invalid value for {$rowLabel} in year {$year}.");
            }

            $values[$year] = number_format((float) $value, 2, '.', '');
        }

        return $values;
    }
}
