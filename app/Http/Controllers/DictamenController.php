<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDictamenRequest;
use App\Models\Dictamen;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

        $dictamen->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Dictamen aprobado.');
    }
}