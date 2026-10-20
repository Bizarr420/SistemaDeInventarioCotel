<?php

namespace App\Http\Controllers;

use App\Http\Requests\ObsoletenessCriterionRequest;
use App\Models\ObsoletenessCriterion;
use App\Traits\AuthorizesPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ObsoletenessCriterionController extends Controller
{
    use AuthorizesPermission;

    public function index(): View
    {
        $this->authorizePermission('assets.criteria.manage');
        $criteria = ObsoletenessCriterion::orderBy('name')->get();

        return view('obsoleteness_criteria.index', compact('criteria'));
    }

    public function create(): View
    {
        $this->authorizePermission('assets.criteria.manage');

        return view('obsoleteness_criteria.create');
    }

    public function store(ObsoletenessCriterionRequest $request): RedirectResponse
    {
        $this->authorizePermission('assets.criteria.manage');
        ObsoletenessCriterion::create($request->validated());

        return redirect()->route('obsoleteness_criteria.index')->with('success', 'Criterio creado.');
    }

    public function edit(ObsoletenessCriterion $obsoletenessCriterion): View
    {
        $this->authorizePermission('assets.criteria.manage');

        return view('obsoleteness_criteria.edit', compact('obsoletenessCriterion'));
    }

    public function update(ObsoletenessCriterionRequest $request, ObsoletenessCriterion $obsoletenessCriterion): RedirectResponse
    {
        $this->authorizePermission('assets.criteria.manage');
        $obsoletenessCriterion->update($request->validated());

        return redirect()->route('obsoleteness_criteria.index')->with('success', 'Criterio actualizado.');
    }

    public function destroy(ObsoletenessCriterion $obsoletenessCriterion): RedirectResponse
    {
        $this->authorizePermission('assets.criteria.manage');
        $obsoletenessCriterion->delete();

        return redirect()->route('obsoleteness_criteria.index')->with('success', 'Criterio eliminado.');
    }
}