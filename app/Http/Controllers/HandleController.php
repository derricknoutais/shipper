<?php

namespace App\Http\Controllers;

use App\Handle;
use App\Product;
use Illuminate\Http\Request;

class HandleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $handles = Handle::orderBy('name')->get();
        foreach ($handles as $handle ) {
            $handle->product_example = Product::where('handle_id', $handle->id)->first();
        }
        return view('handles.index', compact('handles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $products = Product::all();
        foreach ($products as $product ) {
            $product->update([
                'handle_id' => Handle::where('name', $product->handle)->first()->id
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Handle  $handle
     * @return \Illuminate\Http\Response
     */
    public function show(Handle $handle)
    {
        $handle->loadMissing('brands');
        return $handle;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Handle  $handle
     * @return \Illuminate\Http\Response
     */
    public function edit(Handle $handle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Handle  $handle
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Handle $handle)
    {
        // return $request['handle'][$request['field']];

        $handle->update([
            $request['field'] => $request['handle'][$request['field']]
        ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Handle  $handle
     * @return \Illuminate\Http\Response
     */
    public function destroy(Handle $handle)
    {
        //
    }
}
