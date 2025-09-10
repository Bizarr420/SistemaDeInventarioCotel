<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use Illuminate\Http\Request;

class MovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = \App\Models\Movement::with('product','user')->latest()->paginate(20);
        $products = \App\Models\Product::orderBy('name')->get();
        return view('movements.index', compact('items','products'));
    }

    public function storeIn() {
    request()->validate([
        'product_id'=>'required|exists:products,id',
        'quantity'=>'required|integer|min:1',
        'note'=>'nullable|string|max:255',
    ]);

    \DB::transaction(function() {
        $p = \App\Models\Product::lockForUpdate()->findOrFail(request('product_id'));
        $p->increment('stock', (int)request('quantity'));
        \App\Models\Movement::create([
        'product_id'=>$p->id,
        'type'=>'in',
        'quantity'=> (int)request('quantity'),
        'note'=> request('note'),
        'user_id'=> auth()->id(),
        ]);
    });

    return back()->with('ok','Entrada registrada');
    }

    public function storeOut() {
    request()->validate([
        'product_id'=>'required|exists:products,id',
        'quantity'=>'required|integer|min:1',
        'note'=>'nullable|string|max:255',
    ]);

    \DB::transaction(function() {
        $p = \App\Models\Product::lockForUpdate()->findOrFail(request('product_id'));
        $qty = (int)request('quantity');
        if ($p->stock < $qty) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'quantity'=>'Stock insuficiente',
        ]);
        }
        $p->decrement('stock', $qty);
        \App\Models\Movement::create([
        'product_id'=>$p->id,
        'type'=>'out',
        'quantity'=>$qty,
        'note'=> request('note'),
        'user_id'=> auth()->id(),
        ]);
    });

    return back()->with('ok','Salida registrada');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Movement $movement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Movement $movement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Movement $movement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Movement $movement)
    {
        //
    }
}
