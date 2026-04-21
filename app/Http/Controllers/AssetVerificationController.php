<?php

namespace App\Http\Controllers;

use App\Models\AssetVerification;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetVerificationController extends Controller
{
    public function index(): View
    {
        $verifications = AssetVerification::with(['asset', 'verifier'])
            ->latest('verified_at')
            ->latest('id')
            ->paginate(15);

        return view('fixed-assets.verifications.index', compact('verifications'));
    }

    public function create(Request $request): View
    {
        $assets = Product::where('type', 'asset')->orderBy('name_item')->get();
        $selectedAssetId = $request->integer('asset_id');

        return view('fixed-assets.verifications.create', compact('assets', 'selectedAssetId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'verified_at' => 'required|date',
            'status' => 'required|in:operativo,falla,deteriorado,obsoleto',
            'deterioration_level' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string',
            'next_verification_at' => 'nullable|date|after_or_equal:verified_at',
        ]);

        $asset = Product::where('id', $validated['product_id'])
            ->where('type', 'asset')
            ->firstOrFail();

        $validated['verified_by'] = auth()->id();

        AssetVerification::create($validated);

        $updates = [
            'asset_status' => $validated['status'],
        ];

        if ($validated['status'] === 'obsoleto') {
            $updates['obsolescence_status'] = 'obsolete';
            $updates['obsolete_disposition_status'] = $asset->obsolete_disposition_status ?: 'pendiente';
        } else {
            $updates['obsolescence_status'] = 'active';
            $updates['obsolete_disposition_status'] = null;
        }

        $asset->update($updates);

        return redirect()->route('fixed-assets.verifications.index')
            ->with('success', 'Verificacion registrada correctamente.');
    }
}
