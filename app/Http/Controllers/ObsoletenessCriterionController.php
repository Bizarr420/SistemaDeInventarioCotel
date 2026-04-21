<?php

namespace App\Http\Controllers;

use App\Http\Requests\ObsoletenessCriterionRequest;
use App\Models\ObsoletenessCriterion;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ObsoletenessCriterionController extends Controller
{
    public function index(): View
    {
        $criteria = ObsoletenessCriterion::orderBy('name')->get();

        return view('obsoleteness_criteria.index', compact('criteria'));
    }

    public function create(): View
    {
        return view('obsoleteness_criteria.create');
    }

    public function store(ObsoletenessCriterionRequest $request): RedirectResponse
    {
        ObsoletenessCriterion::create($request->validated());

        return redirect()->route('obsoleteness_criteria.index')->with('success', 'Criterio creado.');
    }

    public function edit(ObsoletenessCriterion $obsoletenessCriterion): View
    {
        return view('obsoleteness_criteria.edit', compact('obsoletenessCriterion'));
    }

    public function update(ObsoletenessCriterionRequest $request, ObsoletenessCriterion $obsoletenessCriterion): RedirectResponse
    {
        $obsoletenessCriterion->update($request->validated());

        return redirect()->route('obsoleteness_criteria.index')->with('success', 'Criterio actualizado.');
    }

    public function destroy(ObsoletenessCriterion $obsoletenessCriterion): RedirectResponse
    {
        $obsoletenessCriterion->delete();

        return redirect()->route('obsoleteness_criteria.index')->with('success', 'Criterio eliminado.');
    }
}