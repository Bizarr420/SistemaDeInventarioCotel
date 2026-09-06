<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FixedAssetController extends Controller
{
    /**
     * Display a listing of fixed assets (computers, equipment, etc.).
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:150',
            'category_id' => 'nullable|integer|exists:categories,id',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'branch' => 'nullable|string|max:120',
            'location' => 'nullable|string|max:150',
            'assignee' => 'nullable|string|max:150',
            'asset_status' => 'nullable|in:operativo,falla,deteriorado,obsoleto',
            'substatus' => 'nullable|in:pendiente,vendido,destruido',
            'min_value' => 'nullable|numeric|min:0',
            'max_value' => 'nullable|numeric|min:0|gte:min_value',
            'min_age' => 'nullable|integer|min:0|max:100',
            'max_age' => 'nullable|integer|min:0|max:100|gte:min_age',
            'min_remaining_life' => 'nullable|integer|min:0|max:100',
            'max_remaining_life' => 'nullable|integer|min:0|max:100|gte:min_remaining_life',
            'verification_status' => 'nullable|in:verified,pending',
            'sort' => 'nullable|in:name,value,age,acquisition_date,status,remaining_life',
            'direction' => 'nullable|in:asc,desc',
        ]);

        $query = Product::with(['category', 'supplier', 'latestVerification'])
            ->where('type', 'asset');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($assetQuery) use ($search) {
                $assetQuery->where('name_item', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('internal_code', 'like', "%{$search}%");
            });
        }

        foreach (['category_id', 'supplier_id'] as $field) {
            if (!empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        if (!empty($filters['branch'])) {
            $query->where('location_branch', 'like', '%' . $filters['branch'] . '%');
        }

        if (!empty($filters['location'])) {
            $location = $filters['location'];
            $query->where(function ($assetQuery) use ($location) {
                $assetQuery->where('location_branch', 'like', "%{$location}%")
                    ->orWhere('location_floor', 'like', "%{$location}%")
                    ->orWhere('location_office', 'like', "%{$location}%");
            });
        }

        if (!empty($filters['assignee'])) {
            $assignee = $filters['assignee'];
            $query->where(function ($assetQuery) use ($assignee) {
                $assetQuery->where('assigned_to', 'like', "%{$assignee}%")
                    ->orWhere('assigned_department', 'like', "%{$assignee}%");
            });
        }

        if (!empty($filters['asset_status'])) {
            $query->where('asset_status', $filters['asset_status']);
        }

        if (!empty($filters['substatus'])) {
            $query->where('obsolete_disposition_status', $filters['substatus']);
        }

        foreach (['min_value' => '>=', 'max_value' => '<='] as $field => $operator) {
            if (isset($filters[$field])) {
                $query->where('unit_cost', $operator, $filters[$field]);
            }
        }

        if (isset($filters['min_age'])) {
            $query->whereDate('acquisition_date', '<=', now()->subYears($filters['min_age'])->toDateString());
        }

        if (isset($filters['max_age'])) {
            $query->whereDate('acquisition_date', '>=', now()->subYears($filters['max_age'] + 1)->addDay()->toDateString());
        }

        $remainingLifeExpression = "GREATEST(0, LEAST(100, DATEDIFF(COALESCE(expected_useful_life, DATE_ADD(acquisition_date, INTERVAL useful_life_years YEAR)), CURDATE()) * 100 / NULLIF(DATEDIFF(COALESCE(expected_useful_life, DATE_ADD(acquisition_date, INTERVAL useful_life_years YEAR)), acquisition_date), 0)))";
        if (isset($filters['min_remaining_life'])) {
            $query->whereRaw("{$remainingLifeExpression} >= ?", [$filters['min_remaining_life']]);
        }
        if (isset($filters['max_remaining_life'])) {
            $query->whereRaw("{$remainingLifeExpression} <= ?", [$filters['max_remaining_life']]);
        }

        if (($filters['verification_status'] ?? null) === 'verified') {
            $query->whereHas('verifications');
        } elseif (($filters['verification_status'] ?? null) === 'pending') {
            $query->whereDoesntHave('verifications');
        }

        $sort = $filters['sort'] ?? 'name';
        $direction = $filters['direction'] ?? 'asc';
        $sortColumn = [
            'name' => 'name_item',
            'value' => 'unit_cost',
            'age' => 'acquisition_date',
            'acquisition_date' => 'acquisition_date',
            'status' => 'asset_status',
        ][$sort] ?? null;

        if ($sortColumn) {
            $query->orderBy($sortColumn, $direction);
        } else {
            $query->orderByRaw("{$remainingLifeExpression} {$direction}");
        }

        $assets = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('fixed-assets.index', compact('assets', 'categories', 'suppliers', 'filters'));
    }

    /**
     * Display the specified fixed asset.
     */
    public function show(Product $fixed_asset)
    {
        $asset = $fixed_asset->load([
            'category',
            'supplier',
            'warehouse',
            'latestVerification.verifier',
            'verifications.verifier',
        ]);

        abort_unless($asset->type === 'asset', 404);

        $acquisitionValue = (float) ($asset->acquisition_value ?? (($asset->unit_cost ?? 0) * ($asset->quantity ?? 0)));
        $accountingValue = (float) ($asset->current_accounting_value ?? 0);
        $depreciationAccumulated = max(0, $acquisitionValue - $accountingValue);
        $depreciatedPercentage = $acquisitionValue > 0
            ? min(100, round(($depreciationAccumulated / $acquisitionValue) * 100, 2))
            : 0;
        $ageYears = $asset->acquisition_date ? $asset->acquisition_date->diffInYears(now()) : null;
        $usefulLifeYears = (int) ($asset->useful_life_years ?? 0);
        $remainingLifeYears = $usefulLifeYears > 0 && $ageYears !== null
            ? max(0, $usefulLifeYears - $ageYears)
            : null;
        $consumedPercentage = $usefulLifeYears > 0 && $ageYears !== null
            ? min(100, round(($ageYears / $usefulLifeYears) * 100, 2))
            : null;
        $estimatedValue = (float) ($asset->technical_value ?? $accountingValue);
        $statusReason = is_array($asset->obsolescence_criteria)
            ? collect($asset->obsolescence_criteria)
                ->map(fn ($value, $key) => $key . ': ' . (is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE)))
                ->implode(', ')
            : ($asset->obsolescence_criteria ?: $asset->note);

        return view('fixed-assets.show', compact(
            'asset',
            'acquisitionValue',
            'accountingValue',
            'depreciationAccumulated',
            'depreciatedPercentage',
            'ageYears',
            'usefulLifeYears',
            'remainingLifeYears',
            'consumedPercentage',
            'estimatedValue',
            'statusReason',
        ));
    }

    /**
     * Show the form for creating a new fixed asset.
     */
    public function create()
    {
        $categories = Category::all();
        $suppliers = Supplier::all();

        return view('fixed-assets.create', compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created fixed asset in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'location_branch' => 'required|string|max:120',
            'location_floor' => 'nullable|string|max:80',
            'location_office' => 'nullable|string|max:120',
            'assigned_to' => 'nullable|string|max:150',
            'assigned_department' => 'nullable|string|max:150',
            'quantity' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'sku' => 'nullable|string|unique:products,sku',
            'useful_life_years' => 'nullable|integer|min:1',
            'expected_useful_life' => 'nullable|date',
        ]);

        if (empty($validated['sku'])) {
            $validated['sku'] = 'AF-' . now()->format('YmdHis') . '-' . random_int(100, 999);
        }

        $validated['name_item'] = $validated['name'];
        unset($validated['name']);
        $validated['type'] = 'asset';
        $validated['asset_status'] = 'operativo';
        $validated['obsolete_disposition_status'] = null;

        Product::create($validated);

        return redirect()->route('fixed-assets.index')->with('success', 'Activo fijo creado exitosamente.');
    }

    /**
     * Show the form for editing the specified fixed asset.
     */
    public function edit(Product $fixed_asset)
    {
        $asset = $fixed_asset;
        $categories = Category::all();
        $suppliers = Supplier::all();

        return view('fixed-assets.edit', compact('asset', 'categories', 'suppliers'));
    }

    /**
     * Update the specified fixed asset in storage.
     */
    public function update(Request $request, Product $fixed_asset)
    {
        $asset = $fixed_asset;
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'obsolete_disposition_status' => 'nullable|in:pendiente,vendido,destruido',
            'location_branch' => 'required|string|max:120',
            'location_floor' => 'nullable|string|max:80',
            'location_office' => 'nullable|string|max:120',
            'assigned_to' => 'nullable|string|max:150',
            'assigned_department' => 'nullable|string|max:150',
            'quantity' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'sku' => 'nullable|string|unique:products,sku,' . $asset->id,
            'useful_life_years' => 'nullable|integer|min:1',
            'expected_useful_life' => 'nullable|date',
        ]);

        $currentStatus = $asset->asset_status ?? 'operativo';

        if ($currentStatus === 'obsoleto' && empty($validated['obsolete_disposition_status'])) {
            $validated['obsolete_disposition_status'] = 'pendiente';
        }

        if ($currentStatus !== 'obsoleto') {
            $validated['obsolete_disposition_status'] = null;
        }

        $validated['name_item'] = $validated['name'];
        unset($validated['name']);
        $asset->update($validated);

        return redirect()->route('fixed-assets.index')->with('success', 'Activo fijo actualizado exitosamente.');
    }

    /**
     * Remove the specified fixed asset from storage.
     */
    public function destroy(Product $fixed_asset)
    {
        $asset = $fixed_asset;
        $asset->delete();

        return redirect()->route('fixed-assets.index')->with('success', 'Activo fijo eliminado exitosamente.');
    }

    /**
     * Mark a fixed asset as disposed with a final disposition.
     */
    public function dispose(Request $request, Product $fixed_asset)
    {
        $asset = $fixed_asset;

        $validated = $request->validate([
            'obsolete_disposition_status' => 'required|in:vendido,destruido',
        ]);

        $asset->update([
            'asset_status' => 'obsoleto',
            'obsolescence_status' => 'obsolete',
            'obsolete_disposition_status' => $validated['obsolete_disposition_status'],
        ]);

        return redirect()
            ->route('fixed-assets.edit', $asset)
            ->with('success', 'El activo fue marcado como obsoleto y dado de baja con estado ' . $validated['obsolete_disposition_status'] . '.');
    }

    /**
     * Show migration upload and preview screen for fixed assets.
     */
    public function migrationCreate(Request $request)
    {
        if ($request->boolean('clear_preview')) {
            session()->forget('fixed_assets_migration_preview');

            return redirect()->route('fixed-assets.migration.create')
                ->with('success', 'Previsualizacion limpiada correctamente.');
        }

        return view('fixed-assets.migration', [
            'previewRows' => session('fixed_assets_migration_preview.rows', []),
            'previewSummary' => session('fixed_assets_migration_preview.summary', null),
        ]);
    }

    /**
     * Parse uploaded file and store preview rows in session.
     */
    public function migrationPreview(Request $request)
    {
        $request->validate([
            'migration_file' => 'required|file|mimes:xlsx,xls,csv,pdf|max:10240',
        ]);

        $file = $request->file('migration_file');
        $extension = strtolower((string) $file->getClientOriginalExtension());

        if ($extension === 'xls') {
            return back()->with('error', 'Formato .xls no soportado en este servidor. Guarda el archivo como .xlsx o .csv e intenta nuevamente.');
        }

        $rawRows = in_array($extension, ['xlsx', 'csv'], true)
            ? $this->parseExcelRows($file->getRealPath(), $extension)
            : $this->parsePdfRows($file->getRealPath());

        if (empty($rawRows)) {
            return back()->with('error', 'No se encontraron filas para previsualizar en el archivo cargado.');
        }

        $previewRows = $this->buildPreviewRows($rawRows);

        if (empty($previewRows)) {
            return back()->with('error', 'No se pudo interpretar ninguna fila valida del archivo.');
        }

        $validCount = collect($previewRows)->where('is_valid', true)->count();
        $invalidCount = count($previewRows) - $validCount;

        session([
            'fixed_assets_migration_preview' => [
                'rows' => $previewRows,
                'summary' => [
                    'total' => count($previewRows),
                    'valid' => $validCount,
                    'invalid' => $invalidCount,
                ],
            ],
        ]);

        return redirect()->route('fixed-assets.migration.create')
            ->with('success', 'Previsualizacion generada. Revisa los datos antes de confirmar la migracion.');
    }

    /**
     * Persist previewed valid rows as fixed assets.
     */
    public function migrationStore(Request $request)
    {
        $payload = session('fixed_assets_migration_preview');

        if (empty($payload['rows']) || !is_array($payload['rows'])) {
            return redirect()->route('fixed-assets.migration.create')
                ->with('error', 'No existe una previsualizacion activa. Carga un archivo primero.');
        }

        $rows = collect($payload['rows']);
        $validRows = $rows->where('is_valid', true)->values();

        if ($validRows->isEmpty()) {
            return redirect()->route('fixed-assets.migration.create')
                ->with('error', 'No hay filas validas para migrar. Corrige el archivo y vuelve a previsualizar.');
        }

        DB::transaction(function () use ($validRows) {
            foreach ($validRows as $index => $row) {
                $sku = trim((string) ($row['sku'] ?? ''));
                if ($sku !== '' && Product::where('sku', $sku)->exists()) {
                    $sku = '';
                }

                if ($sku === '') {
                    $sku = 'AF-MIG-' . now()->format('YmdHis') . '-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);
                }

                $categoryId = $row['category_id'] ?? $this->resolveOrCreateCategoryId((string) ($row['category_name'] ?? ''));
                $supplierId = $row['supplier_id'] ?? $this->resolveOrCreateSupplierId((string) ($row['supplier_name'] ?? ''));

                if ($categoryId === null || $supplierId === null) {
                    continue;
                }

                Product::create([
                    'name_item' => $row['name'],
                    'type' => 'asset',
                    'asset_status' => 'operativo',
                    'description' => $row['description'],
                    'category_id' => $categoryId,
                    'supplier_id' => $supplierId,
                    'location_branch' => $row['location_branch'],
                    'location_floor' => $row['location_floor'],
                    'location_office' => $row['location_office'],
                    'assigned_to' => $row['assigned_to'],
                    'assigned_department' => $row['assigned_department'],
                    'quantity' => $row['quantity'],
                    'unit_cost' => $row['unit_cost'],
                    'sku' => $sku,
                    'useful_life_years' => $row['useful_life_years'],
                    'expected_useful_life' => $row['expected_useful_life'],
                ]);
            }
        });

        $importedCount = $validRows->count();
        session()->forget('fixed_assets_migration_preview');

        return redirect()->route('fixed-assets.index')
            ->with('success', "Migracion completada: {$importedCount} activos fijos cargados correctamente.");
    }

    private function parseExcelRows(string $path, string $extension): array
    {
        if ($extension === 'csv') {
            return $this->parseCsvRows($path);
        }

        if ($extension === 'xlsx') {
            return $this->parseXlsxRows($path);
        }

        return [];
    }

    private function parseCsvRows(string $path): array
    {
        $handle = @fopen($path, 'r');
        if (!$handle) {
            return [];
        }

        $headers = null;
        $mapped = [];

        while (($row = fgetcsv($handle)) !== false) {
            if ($headers === null) {
                $headers = array_map(fn ($value) => $this->normalizeHeader((string) $value), $row);
                continue;
            }

            $assoc = [];
            foreach ($headers as $i => $header) {
                if ($header === '') {
                    continue;
                }
                $assoc[$header] = isset($row[$i]) ? trim((string) $row[$i]) : '';
            }
            $mapped[] = $assoc;
        }

        fclose($handle);

        return $mapped;
    }

    private function parseXlsxRows(string $path): array
    {
        if (!class_exists('ZipArchive')) {
            return [];
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $shared = @simplexml_load_string($sharedStringsXml);
            if ($shared !== false && isset($shared->si)) {
                foreach ($shared->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                        continue;
                    }

                    $text = '';
                    if (isset($si->r)) {
                        foreach ($si->r as $run) {
                            $text .= (string) ($run->t ?? '');
                        }
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            return [];
        }

        $sheet = @simplexml_load_string($sheetXml);
        if ($sheet === false || !isset($sheet->sheetData->row)) {
            return [];
        }

        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $ref = (string) ($c['r'] ?? '');
                $col = preg_replace('/\d+/', '', $ref) ?? '';
                $index = $this->columnToIndex($col);
                $type = (string) ($c['t'] ?? '');

                $value = '';
                if ($type === 's') {
                    $sharedIndex = (int) ($c->v ?? 0);
                    $value = $sharedStrings[$sharedIndex] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) ($c->is->t ?? '');
                } else {
                    $value = (string) ($c->v ?? '');
                }

                $cells[$index] = trim($value);
            }

            if (!empty($cells)) {
                ksort($cells);
                $rows[] = array_values($cells);
            }
        }

        if (count($rows) < 2) {
            return [];
        }

        $headers = array_map(fn ($value) => $this->normalizeHeader((string) $value), $rows[0]);
        $mapped = [];

        foreach (array_slice($rows, 1) as $row) {
            if (!is_array($row)) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $i => $header) {
                if ($header === '') {
                    continue;
                }
                $assoc[$header] = isset($row[$i]) ? trim((string) $row[$i]) : '';
            }
            $mapped[] = $assoc;
        }

        return $mapped;
    }

    private function columnToIndex(string $column): int
    {
        $column = strtoupper($column);
        $length = strlen($column);
        $index = 0;

        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($column[$i]) - 64);
        }

        return max(0, $index - 1);
    }

    private function parsePdfRows(string $path): array
    {
        $text = $this->extractTextFromPdf($path);
        if (trim($text) === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $lines = array_values(array_filter(array_map('trim', $lines), fn ($line) => $line !== ''));

        if (count($lines) < 2) {
            return [];
        }

        $headerLine = $lines[0];
        $delimiter = $this->detectDelimiter($headerLine);
        if ($delimiter === null) {
            return [];
        }

        $headers = array_map(fn ($value) => $this->normalizeHeader((string) $value), str_getcsv($headerLine, $delimiter));
        $mapped = [];

        foreach (array_slice($lines, 1) as $line) {
            $cells = str_getcsv($line, $delimiter);
            if (empty($cells)) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $i => $header) {
                if ($header === '') {
                    continue;
                }
                $assoc[$header] = isset($cells[$i]) ? trim((string) $cells[$i]) : '';
            }
            $mapped[] = $assoc;
        }

        return $mapped;
    }

    private function buildPreviewRows(array $rawRows): array
    {
        $preview = [];

        foreach ($rawRows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $name = $this->pick($row, ['nombre_activo', 'nombre', 'name', 'name_item']);
            $categoryName = $this->pick($row, ['categoria', 'category']);
            $supplierName = $this->pick($row, ['proveedor', 'supplier']);

            $normalized = [
                'line' => $index + 2,
                'name' => $name,
                'description' => $this->pick($row, ['descripcion', 'description']),
                'category_name' => $categoryName,
                'supplier_name' => $supplierName,
                'location_branch' => $this->pick($row, ['sucursal', 'location_branch']),
                'location_floor' => $this->pick($row, ['piso', 'location_floor']),
                'location_office' => $this->pick($row, ['oficina', 'location_office']),
                'assigned_to' => $this->pick($row, ['encargado', 'asignado', 'assigned_to']),
                'assigned_department' => $this->pick($row, ['departamento', 'assigned_department']),
                'quantity' => (int) $this->parseNumber($this->pick($row, ['cantidad', 'quantity']), 1),
                'unit_cost' => $this->parseNumber($this->pick($row, ['costo_unitario', 'costo', 'unit_cost']), 0),
                'sku' => $this->pick($row, ['sku']),
                'useful_life_years' => $this->nullableInt($this->pick($row, ['vida_util_anios', 'vida_util', 'useful_life_years'])),
                'expected_useful_life' => $this->normalizeDate($this->pick($row, ['fecha_fin_vida_util', 'expected_useful_life'])),
            ];

            if ($this->isRowEmpty($normalized)) {
                continue;
            }

            $errors = [];
            $warnings = [];

            if ($normalized['name'] === '') {
                $errors[] = 'Falta el nombre del activo.';
            }
            if ($normalized['location_branch'] === '') {
                $errors[] = 'Falta la sucursal.';
            }
            if ($normalized['quantity'] < 1) {
                $errors[] = 'La cantidad debe ser mayor o igual a 1.';
            }
            if ($normalized['unit_cost'] < 0) {
                $errors[] = 'El costo unitario no puede ser negativo.';
            }

            if ($categoryName === '') {
                $errors[] = 'Falta la categoria.';
            }

            if ($supplierName === '') {
                $errors[] = 'Falta el proveedor.';
            }

            $categoryId = $this->resolveCategoryId($categoryName);
            if ($categoryName !== '' && $categoryId === null) {
                $warnings[] = "Categoria nueva, se creara al confirmar: {$categoryName}";
            }

            $supplierId = $this->resolveSupplierId($supplierName);
            if ($supplierName !== '' && $supplierId === null) {
                $warnings[] = "Proveedor nuevo, se creara al confirmar: {$supplierName}";
            }

            $normalized['category_id'] = $categoryId;
            $normalized['supplier_id'] = $supplierId;
            $normalized['is_valid'] = empty($errors);
            $normalized['errors'] = $errors;
            $normalized['warnings'] = $warnings;

            $preview[] = $normalized;
        }

        return array_slice($preview, 0, 500);
    }

    private function pick(array $row, array $keys): string
    {
        foreach ($keys as $key) {
            $normalizedKey = $this->normalizeHeader($key);
            if (array_key_exists($normalizedKey, $row)) {
                return trim((string) $row[$normalizedKey]);
            }
        }

        return '';
    }

    private function resolveCategoryId(string $name): ?int
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        return Category::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->value('id');
    }

    private function resolveSupplierId(string $name): ?int
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        return Supplier::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->value('id');
    }

    private function resolveOrCreateCategoryId(string $name): ?int
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $existingId = $this->resolveCategoryId($name);
        if ($existingId !== null) {
            return $existingId;
        }

        $category = Category::create([
            'name' => $name,
            'description' => 'Creado automaticamente por migracion de activos fijos',
        ]);

        return $category->id;
    }

    private function resolveOrCreateSupplierId(string $name): ?int
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $existingId = $this->resolveSupplierId($name);
        if ($existingId !== null) {
            return $existingId;
        }

        $supplier = Supplier::create([
            'name' => $name,
            'contact' => null,
            'phone' => null,
            'email' => null,
        ]);

        return $supplier->id;
    }

    private function parseNumber(string $value, float|int $default = 0): float
    {
        $clean = str_replace([' ', ','], ['', '.'], trim($value));
        if ($clean === '' || !is_numeric($clean)) {
            return (float) $default;
        }

        return (float) $clean;
    }

    private function nullableInt(string $value): ?int
    {
        $value = trim($value);
        if ($value === '' || !is_numeric($value)) {
            return null;
        }

        $int = (int) $value;
        return $int > 0 ? $int : null;
    }

    private function normalizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function normalizeHeader(string $value): string
    {
        $value = trim(Str::lower($value));
        $value = Str::ascii($value);
        $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? '';
        return trim($value, '_');
    }

    private function detectDelimiter(string $line): ?string
    {
        foreach (['|', ';', "\t", ','] as $delimiter) {
            if (substr_count($line, $delimiter) >= 2) {
                return $delimiter;
            }
        }

        return null;
    }

    private function isRowEmpty(array $row): bool
    {
        return trim((string) ($row['name'] ?? '')) === ''
            && trim((string) ($row['category_name'] ?? '')) === ''
            && trim((string) ($row['supplier_name'] ?? '')) === ''
            && trim((string) ($row['location_branch'] ?? '')) === '';
    }

    private function extractTextFromPdf(string $path): string
    {
        $content = @file_get_contents($path);
        if ($content === false || $content === '') {
            return '';
        }

        preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $content, $matches);
        $streams = $matches[1] ?? [];

        $text = '';

        foreach ($streams as $stream) {
            $decoded = $this->decodePdfStream($stream);
            if ($decoded === '') {
                continue;
            }

            preg_match_all('/\((.*?)\)\s*Tj/s', $decoded, $tjs);
            foreach ($tjs[1] ?? [] as $str) {
                $text .= $this->unescapePdfString($str) . PHP_EOL;
            }

            preg_match_all('/\[(.*?)\]\s*TJ/s', $decoded, $tjsArray);
            foreach ($tjsArray[1] ?? [] as $arr) {
                preg_match_all('/\((.*?)\)/s', $arr, $strings);
                foreach ($strings[1] ?? [] as $str) {
                    $text .= $this->unescapePdfString($str);
                }
                $text .= PHP_EOL;
            }
        }

        return trim($text);
    }

    private function decodePdfStream(string $stream): string
    {
        $stream = ltrim($stream, "\r\n");

        $decoded = @gzuncompress($stream);
        if (is_string($decoded)) {
            return $decoded;
        }

        $decoded = @gzinflate($stream);
        if (is_string($decoded)) {
            return $decoded;
        }

        return $stream;
    }

    private function unescapePdfString(string $value): string
    {
        $value = str_replace(['\\(', '\\)', '\\\\'], ['(', ')', '\\'], $value);
        $value = preg_replace_callback('/\\([0-7]{1,3})/', function ($matches) {
            return chr(octdec($matches[1]));
        }, $value) ?? $value;

        return trim($value);
    }
}
