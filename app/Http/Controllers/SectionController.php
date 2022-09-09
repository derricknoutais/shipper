<?php

namespace App\Http\Controllers;

use App\Events\SectionAdded;
use DB;
use App\Section;
use App\Template;
use App\Sectionnable;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $request->all();
        $section = Section::create([
            'commande_id' => $request['commande'],
            'nom' => $request['section']
        ]);
        DB::table('commandables')->insert([
            'commande_id' => $request['commande'],
            'commandable_id' => $section->id,
            'commandable_type' => 'App\Section'
        ]);
        SectionAdded::dispatch($section);
        $section->loadMissing('sectionnables', 'sectionnables.product', 'sectionnables.article');
        return $section;
    }

    public function addProduct(Request $request){
        // return $request['type'];
        if($request['type'] !== 'App\Template'){
            $sectionnable = Sectionnable::create([
                'section_id' => $request['section'],
                'sectionnable_id' => $request['product']['id'],
                'sectionnable_type' => $request['type'],
                'quantite' => $request['product']['quantite']
            ]);
            return $sectionnable;
        } else {
            // $template = Template::where('id', $request['product']['id'])->with('products')->first();
            // $products = $template->products;
            foreach ($request['product']['products'] as $product) {
                DB::table('sectionnables')->insert([
                    'section_id' => $request['section'],
                    'sectionnable_id' => $product['id'],
                    'sectionnable_type' => 'App\Product',
                    'quantite' => $product['pivot']['quantite']
                ]);
            }
            // return $products;

            // $commande->loadMissing('products', 'templates', 'templates.products', 'sections', 'sections.products', 'demandes', 'demandes.sectionnables', 'bonsCommandes', 'bonsCommandes.sectionnables', 'factures');
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function show(Section $section)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function edit(Section $section)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Section $section)
    {
        $section->update([
            'nom' => $request['nom']
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function destroy(Section $section)
    {
        $section->delete();
    }

    public function destroySectionnable ($sectionnable, $section){
        // return $sectionnable;
        $sectionnables = Sectionnable::where(['sectionnable_id' => $sectionnable, 'section_id' => $section])->get();
        foreach ($sectionnables as $sectionnable ) {
            $sectionnable->delete();
        }
        // $sectionnable->delete();
    }

    public function patchSectionnable(Request $request){
        DB::table('sectionnables')->where('id', $request['id'])->update([
            $request['field'] => $request['value']
        ]);
        return 1;
    }
}
