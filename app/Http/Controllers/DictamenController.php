<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDictamenRequest;
use App\Models\AccountingAdjustment;
use App\Models\Dictamen;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class DictamenController extends Controller
{
    public function index(): View
    {
        $dictamens = Dictamen::with(['product', 'user'])->orderByDesc('created_at')->paginate(20);

        return view('dictamens.index', compact('dictamens'));
    }

    public function create(Product $product): View
    {
        return view('dictamens.create', compact('product'));
    }

    public function store(StoreDictamenRequest $request)
    {
        Dictamen::create([
            'product_id' => $request->product_id,
            'user_id' => auth()->id(),
            'content' => $request->content,
        ]);

        return redirect()->route('dictamens.index')->with('success', 'Dictamen creado.');
    }

    public function approve(Dictamen $dictamen)
    {
        // Solo contable o auditor puede aprobar
        if (!in_array(auth()->user()->role, ['contable', 'auditor'])) {
            abort(403);
        }

        DB::transaction(function () use ($dictamen) {
            $dictamen->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $product = $dictamen->product()->first();
            $technicalValue = (float) ($product?->technical_value ?? 0);
            $accountingValue = (float) ($product?->current_accounting_value ?? 0);
            $adjustmentType = $this->determineAdjustmentType($product);
            $recognizedAmount = $this->calculateRecognizedAmount($adjustmentType, $technicalValue, $accountingValue);
            $status = $recognizedAmount > 0 ? 'posted' : 'no_adjustment';

            AccountingAdjustment::updateOrCreate(
                ['dictamen_id' => $dictamen->id],
                [
                    'product_id' => $dictamen->product_id,
                    'generated_by' => $dictamen->user_id,
                    'approved_by' => auth()->id(),
                    'adjustment_type' => $adjustmentType,
                    'status' => $status,
                    'technical_value' => $technicalValue,
                    'current_accounting_value' => $accountingValue,
                    'recognized_amount' => $recognizedAmount,
                    'description' => $this->buildAdjustmentDescription($adjustmentType, $recognizedAmount),
                    'posted_at' => now(),
                    'traceability' => [
                        'dictamen_id' => $dictamen->id,
                        'approved_by' => auth()->id(),
                        'approved_at' => now()->toDateTimeString(),
                        'adjustment_type' => $adjustmentType,
                        'technical_value' => $technicalValue,
                        'accounting_value' => $accountingValue,
                        'recognized_amount' => $recognizedAmount,
                    ],
                ]
            );
        });

        return redirect()->back()->with('success', 'Dictamen aprobado.');
    }

    private function determineAdjustmentType(?Product $product): string
    {
        if ($product && $product->asset_status === 'obsoleto' && in_array($product->obsolete_disposition_status, ['vendido', 'destruido'], true)) {
            return 'disposal';
        }

        return 'deterioration';
    }

    private function calculateRecognizedAmount(string $adjustmentType, float $technicalValue, float $accountingValue): float
    {
        if ($adjustmentType === 'disposal') {
            return max(0, $accountingValue);
        }

        return max(0, $accountingValue - $technicalValue);
    }

    private function buildAdjustmentDescription(string $adjustmentType, float $recognizedAmount): string
    {
        if ($recognizedAmount <= 0) {
            return 'Dictamen aprobado sin ajuste contable por diferencia favorable o inexistente.';
        }

        return $adjustmentType === 'disposal'
            ? 'Reconocimiento contable por baja/disposición final del activo generado desde dictamen técnico.'
            : 'Reconocimiento contable de deterioro generado desde dictamen técnico.';
    }
}