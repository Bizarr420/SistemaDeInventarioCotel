<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\Product;
use App\Models\ProductStock;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function financialNotes(): View
    {
        $notes = [
            [
                'code' => 'Nota 9',
                'title' => 'Inventarios',
                'description' => 'Composición del saldo de inventarios en almacenes.',
                'totals' => ['2024' => 13545146.21, '2023' => 14456625.50],
                'rows' => [
                    ['code' => '17010101', 'detail' => 'Papelería y Útiles de Oficina', '2024' => 60333.61, '2023' => 71381.49],
                    ['code' => '17010301', 'detail' => 'Materiales Repuestos y Suministros', '2024' => 7775750.06, '2023' => 8344775.86],
                    ['code' => '17010401', 'detail' => 'Combustibles Lubricantes', '2024' => 200.20, '2023' => 208.51],
                    ['code' => '17010501', 'detail' => 'Materiales de Seguridad (Equipo de Trabajo)', '2024' => 12568.08, '2023' => 13167.80],
                    ['code' => '17020501', 'detail' => 'Otros inventarios', '2024' => 8008663.03, '2023' => 8435438.65],
                    ['code' => '17030101', 'detail' => 'Provisión para obsolescencia inventarios', '2024' => -2312368.77, '2023' => -2408346.91],
                ],
            ],
            [
                'code' => 'Nota 11',
                'title' => 'Activos fijos',
                'description' => 'Composición del rubro de activos fijos expuesto en los estados financieros.',
                'totals' => ['2024' => 3693575694.68, '2023' => 3694912310.46],
                'rows' => [
                    ['code' => '23010301', 'detail' => 'Terrenos de edificaciones en general', '2024' => 126281386.60, '2023' => 126281386.55],
                    ['code' => '24010301', 'detail' => 'Edificaciones en general', '2024' => 166903078.23, '2023' => 166903078.44],
                    ['code' => '25010101', 'detail' => 'Redes aéreas', '2024' => 745793754.43, '2023' => 745688538.78],
                    ['code' => '25010102', 'detail' => 'Redes Aéreas S.Cotel', '2024' => 18087741.51, '2023' => 18087741.41],
                    ['code' => '25010201', 'detail' => 'Redes subterráneas', '2024' => 479180579.71, '2023' => 480957136.04],
                    ['code' => '25010202', 'detail' => 'Redes subterráneas S.Cotel', '2024' => 15552920.93, '2023' => 15552911.99],
                    ['code' => '25010301', 'detail' => 'Canalizaciones', '2024' => 252541690.47, '2023' => 252541690.35],
                    ['code' => '25010401', 'detail' => 'Otros planta externa', '2024' => 81743967.14, '2023' => 81723539.35],
                    ['code' => '25010402', 'detail' => 'Otros planta Externa S.Cotel', '2024' => 17644.92, '2023' => 17644.94],
                    ['code' => '25020101', 'detail' => 'Equipos de radio', '2024' => 12628108.93, '2023' => 12628108.92],
                    ['code' => '25020102', 'detail' => 'Equipos de radio S.Cotel', '2024' => 1460717.97, '2023' => 1460718.06],
                    ['code' => '25020201', 'detail' => 'Equipos de multicanales-multiples', '2024' => 124377893.88, '2023' => 124370090.72],
                    ['code' => '25020301', 'detail' => 'Torres de transmisión', '2024' => 5998961.40, '2023' => 5998961.67],
                    ['code' => '25020401', 'detail' => 'Antenas de transmisión', '2024' => 19263679.35, '2023' => 19263680.50],
                    ['code' => '25020402', 'detail' => 'Antenas de transmisión S.Cotel', '2024' => 411401.56, '2023' => 411401.57],
                    ['code' => '25020501', 'detail' => 'Multiacceso rural', '2024' => 32209787.61, '2023' => 32209789.99],
                    ['code' => '25020601', 'detail' => 'Equipos de transmisión de datos', '2024' => 4328638.93, '2023' => 4205443.95],
                    ['code' => '25020602', 'detail' => 'Tranceptor', '2024' => 7623998.83, '2023' => 7623998.90],
                    ['code' => '25020603', 'detail' => 'Equipos de transmisión de datos S.Cotel', '2024' => 325978.79, '2023' => 325978.79],
                    ['code' => '25020609', 'detail' => 'Transmisor', '2024' => 397581.60, '2023' => 407303.41],
                    ['code' => '25030101', 'detail' => 'Centrales locales', '2024' => 1100094638.66, '2023' => 1100078517.12],
                    ['code' => '25030201', 'detail' => 'Otros eq. de conmutación', '2024' => 4708484.05, '2023' => 4708484.11],
                    ['code' => '25030301', 'detail' => 'Nodos ADSL', '2024' => 13926477.90, '2023' => 13921107.42],
                    ['code' => '25040101', 'detail' => 'Baterías', '2024' => 30603209.61, '2023' => 30601625.41],
                    ['code' => '25040102', 'detail' => 'Sum. Interrumpido/Energía UPS', '2024' => 6462931.83, '2023' => 6461743.72],
                    ['code' => '25040103', 'detail' => 'Baterías S.Cotel', '2024' => 23722.06, '2023' => 23722.04],
                    ['code' => '25040201', 'detail' => 'Convertidores', '2024' => 3076519.66, '2023' => 3076520.74],
                    ['code' => '25040202', 'detail' => 'Convertidores S.Cotel', '2024' => 2203242.32, '2023' => 2203242.21],
                    ['code' => '25040301', 'detail' => 'Generadores', '2024' => 20306276.88, '2023' => 20306276.80],
                    ['code' => '25040302', 'detail' => 'Generadores S.Cotel', '2024' => 12227.54, '2023' => 12227.56],
                    ['code' => '25040401', 'detail' => 'Inversores', '2024' => 379682.44, '2023' => 379682.45],
                    ['code' => '25040501', 'detail' => 'Rectificadores', '2024' => 35029154.02, '2023' => 35029153.86],
                    ['code' => '25040601', 'detail' => 'Paneles y arreglos', '2024' => 5158786.40, '2023' => 5158786.00],
                    ['code' => '25050101', 'detail' => 'Teléfonos públicos', '2024' => 21416266.17, '2023' => 21416277.58],
                    ['code' => '25050201', 'detail' => 'Cabinas telefónicas', '2024' => 1848046.35, '2023' => 1848041.41],
                ],
            ],
            [
                'code' => 'Nota 12',
                'title' => 'Depreciación acumulada activos fijos',
                'description' => 'Depreciación acumulada al 31 de diciembre de 2024 y 2023.',
                'totals' => ['2024' => 3347599253.20, '2023' => 3311769226.93],
                'rows' => [
                    ['code' => '26010301', 'detail' => 'Deprec. Acum. Edificac. en Gral.', '2024' => -156071619.01, '2023' => -154401847.38],
                    ['code' => '26020101', 'detail' => 'Deprec. Acum. Redes Aéreas', '2024' => -684145098.78, '2023' => -677853889.09],
                    ['code' => '26020102', 'detail' => 'Deprec. Acum. Redes Aéreas S.Cotel', '2024' => -12398094.09, '2023' => -11855681.97],
                    ['code' => '26020201', 'detail' => 'Deprec. Acum. Redes Subterráneas', '2024' => -443206753.43, '2023' => -441075028.97],
                    ['code' => '26090101', 'detail' => 'Deprec. Acum. Vehículos Automotores', '2024' => -43670927.62, '2023' => -43670927.70],
                    ['code' => '26090102', 'detail' => 'Deprec. Acum. Vehículos Automoto S.Cotel', '2024' => -508387.93, '2023' => -508388.00],
                    ['code' => '26100101', 'detail' => 'Deprec. Acum. Equip./Medición y Herramientas', '2024' => -33391036.18, '2023' => -33194391.12],
                    ['code' => '26100102', 'detail' => 'Deprec. Acum. Medic. y Herram. S.Cotel', '2024' => -194266.52, '2023' => -194266.52],
                    ['code' => '26100201', 'detail' => 'Deprec. Acum. Equipos Auxiliares', '2024' => -5290513.92, '2023' => -5289609.99],
                    ['code' => '26100301', 'detail' => 'Deprec. Acum. Equipos de Comunicación', '2024' => -2040519.34, '2023' => -2037185.41],
                    ['code' => '26100401', 'detail' => 'Deprec. Acum. Cableado Estructurado', '2024' => -60293.24, '2023' => -60293.22],
                    ['code' => '26100501', 'detail' => 'Deprec. Acum. Equip. Aux. y Soporte P expl', '2024' => -103374.03, '2023' => -103374.30],
                    ['code' => '26100502', 'detail' => 'Deprec. Acum. Centro/Gestión de Red', '2024' => -10108948.67, '2023' => -10082594.62],
                    ['code' => '26110102', 'detail' => 'Deprec. Acum. Eq. Atención Autom.', '2024' => -24115.80, '2023' => -21306.15],
                    ['code' => '26120101', 'detail' => 'Deprec. Acum. Terminal de Red NTA (ISDN)', '2024' => -14415.30, '2023' => -14415.32],
                    ['code' => '26120201', 'detail' => 'Deprec. Acum. Terminal de redes NT (ISDN)', '2024' => -177845.48, '2023' => -177845.30],
                    ['code' => '26130101', 'detail' => 'Deprec. Acum. Climatizac. y Aire Acondic.', '2024' => -22824950.19, '2023' => -22751623.34],
                    ['code' => '26130102', 'detail' => 'Deprec. Acum. Climat. Aire Acond S.Cotel', '2024' => -87379.67, '2023' => -87379.87],
                    ['code' => '26160101', 'detail' => 'Deprec. Acum. COTEL TV', '2024' => -89287244.65, '2023' => -86403945.18],
                ],
            ],
            [
                'code' => 'Nota 13',
                'title' => 'Participación en sociedades',
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

        return view('reports.financial-notes', compact('notes'));
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
        $products = Product::where('type', 'asset')->get()->map(function ($product) {
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
        $products = Product::where('type', 'asset')
            ->whereNotNull('technical_value')
            ->whereNotNull('current_accounting_value')
            ->get();

        return view('reports.comparative', compact('products'));
    }

    public function exportObsolescence(Request $request)
    {
        $products = Product::where('type', 'asset')->get()->map(function ($product) {
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
            return $pdf->download('reporte_obsolescencia_' . now()->format('Y-m-d') . '.pdf');
        }

        if ($request->format === 'excel') {
            return Excel::download(new \App\Exports\ObsolescenceExport($products), 'reporte_obsolescencia_' . now()->format('Y-m-d') . '.xlsx');
        }

        return back()->with('error', 'Formato no soportado');
    }
}
