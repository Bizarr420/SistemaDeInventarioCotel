<?php

namespace App\Http\Controllers;

use App\Exports\ObsolescenceExport;
use App\Models\FixedAsset;
use App\Models\InventoryBalanceSnapshot;
use App\Models\Movement;
use App\Models\Product;
use App\Models\ProductStock;
use App\Services\FinancialNotesReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function financialNotes(Request $request, FinancialNotesReportService $financialNotes): View
    {
        $filters = $request->validate([
            'management' => 'nullable|in:all,2023,2024,2025,2026',
            'cutoff_date' => ['nullable', 'date', 'after_or_equal:2026-01-01', 'before_or_equal:today'],
        ]);
        $selectedManagement = $filters['management'] ?? 'all';
        $cutoffDate = $filters['cutoff_date'] ?? now()->toDateString();

        $inventorySnapshots = InventoryBalanceSnapshot::query()
            ->whereIn('balance_year', [2023, 2024, 2025, 2026])
            ->orderBy('account_code')
            ->get()
            ->groupBy('account_code');

        $inventoryYears = [2024, 2023, 2025, 2026];
        $inventoryRows = $inventorySnapshots->map(function ($snapshots): array {
            $first = $snapshots->first();

            return [
                'code' => $first->account_code,
                'detail' => $first->detail,
                'values' => $snapshots->mapWithKeys(
                    fn (InventoryBalanceSnapshot $snapshot): array => [
                        (string) $snapshot->balance_year => (float) $snapshot->amount,
                    ]
                )->all(),
                'estimated_years' => $snapshots
                    ->where('is_estimated', true)
                    ->pluck('balance_year')
                    ->map(fn (int $year): string => (string) $year)
                    ->all(),
            ];
        })->values()->all();

        $inventoryTotals = [];
        foreach ($inventoryYears as $year) {
            $inventoryTotals[(string) $year] = array_sum(array_map(
                fn (array $row): float => $row['values'][(string) $year] ?? 0.0,
                $inventoryRows
            ));
        }

        $notes = [
            [
                'code' => 'Nota 9',
                'title' => 'Inventarios',
                'description' => 'Composici?n del saldo de inventarios en almacenes.',
                'totals' => $inventoryTotals,
                'rows' => $inventoryRows,
                'years' => array_map('strval', $inventoryYears),
                'estimated_years' => ['2025', '2026'],
            ],
            [
                'code' => 'Nota 13',
                'title' => 'Participaci?n en sociedades',
                'description' => 'Cuotas de capital social en ITS.',
                'totals' => ['2024' => 87671771.38, '2023' => 91352693.26],
                'rows' => [
                    ['code' => '20010101', 'detail' => 'Cuotas de Capital Soc. en ITS', '2024' => 87671771.38, '2023' => 91352693.26],
                ],
            ],
            [
                'code' => 'Nota 14',
                'title' => 'Inversiones no relacionadas con el giro',
                'description' => 'Inversiones permanentes y en curso.',
                'totals' => ['2024' => 11834692.21, '2023' => 12444666.17],
                'rows' => [
                    ['code' => '21010101', 'detail' => 'Inversiones Permanentes en Terrenos', '2024' => 822709.21, '2023' => 857250.87],
                    ['code' => '21010201', 'detail' => 'Inversiones Permanentes Otros Activos Fijos', '2024' => 470428.04, '2023' => 470428.05],
                    ['code' => '22010102', 'detail' => 'Inversiones en Curso', '2024' => 10541554.96, '2023' => 10956386.98],
                ],
            ],
        ];

        $assetNotes = $financialNotes->buildAssetNotes($cutoffDate, $selectedManagement);
        array_splice($notes, 1, 0, [$assetNotes['note11'], $assetNotes['note12']]);

        foreach ($notes as &$note) {
            $note['years'] ??= ['2024', '2023'];
            $note['estimated_years'] ??= [];
        }
        unset($note);

        return view('reports.financial-notes', compact(
            'notes',
            'selectedManagement',
            'cutoffDate',
        ));
    }

    public function summary(): View
    {
        return view('reports.summary', [
            'totalProducts' => Product::where('type', 'service')->count(),
            'totalStock' => ProductStock::whereHas('product', function ($query) {
                $query->where('type', 'service');
            })->sum('current_stock'),
            'lastMovements' => Movement::with(['product', 'warehouse'])
                ->whereHas('product', function ($query) {
                    $query->where('type', 'service');
                })
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function kardex(Request $request): View
    {
        $productId = $request->integer('product_id');
        $product = $productId ? Product::where('type', 'service')
            ->with(['movements' => function ($query) {
                $query->orderBy('created_at');
            }, 'movements.warehouse', 'movements.user'])
            ->find($productId) : null;

        return view('reports.kardex', [
            'product' => $product,
            'products' => Product::where('type', 'service')->orderBy('name_item')->get(),
        ]);
    }

    public function deterioration(): View
    {
        $products = FixedAsset::all()->map(function ($product) {
            return [
                'product' => $product,
                'useful_life_percentage' => $product->calculateUsefulLife(),
                'deterioration' => $product->calculateDeterioration(),
                'patrimonial_deviation' => $product->calculatePatrimonialDeviation(),
            ];
        });

        return view('reports.deterioration', compact('products'));
    }

    public function comparative(): View
    {
        $products = FixedAsset::query()
            ->whereNotNull('technical_value')
            ->whereNotNull('current_accounting_value')
            ->get();

        return view('reports.comparative', compact('products'));
    }

    public function exportObsolescence(Request $request)
    {
        $products = FixedAsset::all()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name_item,
                'internal_code' => $product->internal_code,
                'is_obsolete' => $product->isObsolete() ? 'Sí' : 'No',
                'useful_life_percentage' => $product->calculateUsefulLife(),
                'deterioration' => $product->calculateDeterioration(),
                'patrimonial_deviation' => $product->calculatePatrimonialDeviation(),
                'end_of_support' => $product->end_of_support?->format('d/m/Y'),
                'compatibility_status' => $product->compatibility_status,
                'operational_capacity' => $product->operational_capacity,
            ];
        });

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('reports.obsolescence_pdf', compact('products'));

            return $pdf->download('reporte_obsolescencia_'.now()->format('Y-m-d').'.pdf');
        }

        if ($request->format === 'excel') {
            return Excel::download(new ObsolescenceExport($products), 'reporte_obsolescencia_'.now()->format('Y-m-d').'.xlsx');
        }

        return back()->with('error', 'Formato no soportado');
    }
}
