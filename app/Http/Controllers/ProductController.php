<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Fournisseur;
use App\Handle;

class ProductController extends Controller
{
    public function index(Request $request){
        if(isset($request->handle)){
            $handle_filter = $request->handle;
        } else {
            $handle_filter = null;
        }
        if(isset($request->handle)){
            $products = Product::where('handle_id', $request->handle )->with('fournisseurs')->get();
        } else {
            $products = Product::with('fournisseurs')->get();
        }
        $handles = Handle::orderBy('name', 'asc')->get();
        $fournisseurs = Fournisseur::orderBy('nom')->get();

        return view('products.index',compact('products', 'fournisseurs', 'handles', 'handle_filter'));
    }

}
