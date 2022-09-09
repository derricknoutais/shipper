<?php

namespace App\Http\Controllers;

use App\Product;
use App\Template;
use DB;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index(){
        return view('template.menu');
    }

    public function type($type){
        $templates = Template::where('type', $type)->get();
        return view('template.index', compact('templates'));
    }

    public function show(Template $template){
        $template->loadMissing('products');
        $products = Product::all();
        return view('template.show', compact('template', 'products'));
    }

    public function store(Request $request){
        $template = Template::create([
            'name' => $request->name,
            'type' => $request->type
        ]);
        if ($template)
            return redirect()->back();
    }

    public function inventory() {
        $templates = \App\Template::with('products')->get();
        return view('inventory.index', compact('templates'));
    }

    public function createVend (Request $request) {

        $client = new Client();
        $headers = [
            "Authorization" => "Bearer " . env('VEND_TOKEN'),
            'Accept'        => 'application/json',
        ];

        $response = $client->request('POST', 'https://stapog.vendhq.com/api/2.0/consignments', [
            'headers' => $headers,
            'form_params' => [
                
                'outlet_id' => '06bf537b-c77f-11e6-ff13-fb602832ccea',
                'name' => $request->nom,
                'type' => 'STOCKTAKE',
                'status' => 'STOCKTAKE_SCHEDULED',
                'filters' => $request->prods
            ]
        ]);
        $data = json_decode((string) $response->getBody(), true);
    }

}
