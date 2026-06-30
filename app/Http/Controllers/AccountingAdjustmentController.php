<?php

namespace App\Http\Controllers;

use App\Exports\AccountingAdjustmentsExport;
use App\Models\AccountingAdjustment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class AccountingAdjustmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->exportQuery($request);

        $adjustments = $query->paginate(20);

        return view('accounting_adjustments.index', [
            'adjustments' => $adjustments,
            'totalRecognized' => (float) AccountingAdjustment::where('status', 'posted')->sum('recognized_amount'),
            'totalPending' => AccountingAdjustment::where('status', 'pending')->count(),
            'totalDeterioration' => AccountingAdjustment::where('adjustment_type', 'deterioration')->count(),
            'totalDisposal' => AccountingAdjustment::where('adjustment_type', 'disposal')->count(),
        ]);
    }

    public function export(Request $request)
    {
        $adjustments = $this->exportQuery($request)->get()->map(function ($adjustment) {
            return [
                'Fecha' => optional($adjustment->posted_at)->format('d/m/Y H:i') ?? '-',
                'Activo' => $adjustment->product->name_item ?? '-',
                'Código Interno' => $adjustment->product->internal_code ?? '-',
                'Dictamen ID' => $adjustment->dictamen_id ?? '-',
                'Técnico' => (float) $adjustment->technical_value,
                'Contable' => (float) $adjustment->current_accounting_value,
                'Reconocido' => (float) $adjustment->recognized_amount,
                'Estado' => $adjustment->status,
                'Tipo de Ajuste' => $adjustment->adjustment_type,
                'Descripción' => $adjustment->description ?? '-',
            ];
        });

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('accounting_adjustments.pdf', [
                'adjustments' => $this->exportQuery($request)->get(),
            ]);

            return $pdf->download('reconocimientos_contables_' . now()->format('Y-m-d') . '.pdf');
        }

        if ($request->format === 'excel') {
            return Excel::download(
                new AccountingAdjustmentsExport($adjustments),
                'reconocimientos_contables_' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return back()->with('error', 'Formato no soportado');
    }

    protected function exportQuery(Request $request)
    {
        $query = AccountingAdjustment::with(['product', 'dictamen', 'generator', 'approver'])
            ->latest('posted_at')
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('adjustment_type', $request->type);
        }

        return $query;
    }
}