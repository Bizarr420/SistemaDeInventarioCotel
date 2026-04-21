<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\RiskAssessment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiskAssessmentController extends Controller
{
    public function index(Request $request): View
    {
        $riskLevel = $request->get('risk_level');
        $query = RiskAssessment::with(['product', 'responsible']);

        if ($riskLevel) {
            $query->where('risk_level', $riskLevel);
        }

        $assessments = $query->orderBy('risk_level', 'desc')->paginate(20);

        return view('coso.risks.index', compact('assessments', 'riskLevel'));
    }

    public function show(RiskAssessment $assessment): View
    {
        return view('coso.risks.show', compact('assessment'));
    }

    public function create(Product $product = null): View
    {
        $products = Product::all();
        $users = User::all();

        return view('coso.risks.create', compact('products', 'users', 'product'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'risk_category' => 'required|string|max:255',
            'description' => 'required|string',
            'probability' => 'required|integer|min:1|max:5',
            'impact' => 'required|integer|min:1|max:5',
            'mitigation_action' => 'required|string',
            'responsible_user_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        RiskAssessment::create($validated);

        return redirect()->route('coso.risks.index')->with('ok', 'Evaluación de riesgo creada exitosamente.');
    }

    public function update(Request $request, RiskAssessment $assessment)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'closed_at' => 'nullable|date',
        ]);

        $assessment->update($validated);

        return redirect()->back()->with('ok', 'Evaluación actualizada.');
    }

    public function dashboard(): View
    {
        $riskByLevel = RiskAssessment::selectRaw('risk_level, COUNT(*) as total')
            ->groupBy('risk_level')
            ->get();

        $riskByStatus = RiskAssessment::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->where('status', '!=', 'closed')
            ->get();

        $criticalRisks = RiskAssessment::where('risk_level', 'critical')->count();
        $highRisks = RiskAssessment::where('risk_level', 'high')->count();
        $openRisks = RiskAssessment::where('status', 'open')->count();

        return view('coso.risks.dashboard', compact('riskByLevel', 'riskByStatus', 'criticalRisks', 'highRisks', 'openRisks'));
    }
}
