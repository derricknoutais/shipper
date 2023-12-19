<?php

namespace App\Http\Controllers;

use App\Handle;
use App\Product;
use App\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $handle_filter = 1;
        if (isset($request->handle)) {
            $handle_filter = $request->handle;
        } else {
            $request->handle = 1;
        }

        if (isset($request->handle)) {
            $products = Product::where('handle_name', $request->handle)
                ->with('fournisseurs')
                ->get();
        } else {
            $products = Product::with('fournisseurs')->get();
        }
        $handles = Handle::orderBy('name', 'asc')->get();
        $fournisseurs = Fournisseur::orderBy('nom')->get();

        return view('products.index', compact('products', 'fournisseurs', 'handles', 'handle_filter'));
    }
}
