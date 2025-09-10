<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q = request('q');
        $items = \App\Models\Product::with(['category','supplier'])
            ->when($q, fn($x)=>$x->where('name','like',"%$q%")->orWhere('sku','like',"%$q%"))
            ->orderBy('id','desc')->paginate(12)->withQueryString();
        return view('products.index', compact('items','q'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create', [
            'categories'=>\App\Models\Category::orderBy('name')->get(),
            'suppliers'=>\App\Models\Supplier::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\StoreProductRequest $req)
    {
        \App\Models\Product::create($req->validated()+['stock'=>0]);
        return redirect()->route('products.index')->with('ok','Producto creado');
    }

    public function destroy(Product $product) {
    $this->authorize('delete', $product);
    // ...
    }


    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
}
