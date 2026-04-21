<?php

namespace App\Http\Controllers;

use App\Models\CosoControl;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CosoControlController extends Controller
{
    public function index(Request $request): View
    {
        $component = $request->get('component');
        $query = CosoControl::with('responsible');

        if ($component) {
            $query->where('component', $component);
        }

        $controls = $query->paginate(20);
        $components = [
            'environment_control' => 'Ambiente de Control',
            'risk_assessment' => 'Evaluación de Riesgos',
            'control_activities' => 'Actividades de Control',
            'information_communication' => 'Información y Comunicación',
            'monitoring' => 'Supervisión',
        ];

        return view('coso.controls.index', compact('controls', 'components', 'component'));
    }

    public function show(CosoControl $control): View
    {
        return view('coso.controls.show', compact('control'));
    }

    public function create(): View
    {
        $components = [
            'environment_control' => 'Ambiente de Control',
            'risk_assessment' => 'Evaluación de Riesgos',
            'control_activities' => 'Actividades de Control',
            'information_communication' => 'Información y Comunicación',
            'monitoring' => 'Supervisión',
        ];
        $users = User::all();

        return view('coso.controls.create', compact('components', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'component' => 'required|in:environment_control,risk_assessment,control_activities,information_communication,monitoring',
            'control_objective' => 'required|string|max:255',
            'description' => 'required|string',
            'control_type' => 'required|in:preventive,detective',
            'responsible_user_id' => 'nullable|exists:users,id',
        ]);

        CosoControl::create($validated);

        return redirect()->route('coso.controls.index')->with('ok', 'Control COSO creado exitosamente.');
    }

    public function dashboard(): View
    {
        $controlsByComponent = CosoControl::selectRaw('component, COUNT(*) as total')
            ->groupBy('component')
            ->get();

        $controlsByStatus = CosoControl::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get();

        $totalControls = CosoControl::count();
        $activeControls = CosoControl::where('status', 'active')->count();
        $effectiveControls = CosoControl::where('effectiveness', 'high')->count();

        return view('coso.dashboard', compact('controlsByComponent', 'controlsByStatus', 'totalControls', 'activeControls', 'effectiveControls'));
    }
}
