<?php

namespace App\Http\Controllers;

use DB;
use App\Demande;
use App\Product;
use App\Section;
use App\Commande;
use App\Fournisseur;
use App\Sectionnable;
use Illuminate\Http\Request;
use App\Jobs\GenererDemandes;
use App\Exports\DemandeExport;
use App\Imports\DemandeImport;
use Maatwebsite\Excel\Facades\Excel;

class DemandeController extends Controller
{

    public function index(Commande $commande)
    {
        $commande->loadMissing('demandes', 'demandes.sectionnables');
        return view('commande.demandes', compact('commande'));
    }

    public function dispatchSectionnables(Commande $commande)
    {

        dispatch($job = new GenererDemandes($commande));
        return $job->getResponse();

    }

    public function destroySectionnable($id)
    {
        DB::table('demande_sectionnable')->where('id', $id)->delete();
    }

    public function store(Request $request)
    {
        $demande = Demande::create([
            'nom' => $request['fournisseur']['nom'],
            'commande_id' => $request['commande'],
            'fournisseur_id' => $request['fournisseur']['id']
        ]);
        return $demande;
    }

    public function show(Demande $demande)
    {
        $demande->loadMissing(['commande', 'fournisseur', 'bonCommande' ,'sectionnables',
            'sectionnables.product'  => function($query){
                $query->orderBy('name');
            } ,
            'sectionnables.product.handle',
            'sectionnables.article' => function($query){
                $query->orderBy('nom');
            },
            'sectionnables.bon_commande',
            'sectionnables.demandes'
    ]);
        // return $demande->loadMissing(['sectionnables', 'sectionnables.product']);
        $products = Product::all();
        $demandes = Demande::all();
        return view('demande.show', compact('demande', 'demandes', 'products'));
    }

    public function showPrepaDemande(Commande $commande){
        $commande->loadMissing([
            'sections',
            'sections.sectionnables',
            'sections.sectionnables.demandes',
            'sections.products' => function($query){
                $query->orderBy('name');
            },
            'sections.products.handle',
            'sections.articles' => function($query){
                $query->orderBy('nom');
            },
            'demandes',
            'demandes.sectionnables',
            'demandes.sectionnables.product'
        ])->orderBy('sections.products.name');



        $fournisseurs = Fournisseur::all();
        $commandes = Commande::all();

        return view('commande.prepa-demande', compact('commande', 'fournisseurs', 'commandes'));
    }

    public function addSectionnable(Request $request)
    {



        DB::table('demande_sectionnable')->insert([
            'demande_id' => $request['demandes']['id'],
            'sectionnable_id' => $request['products']['pivot']['id'],
            'offre' => 0,
            'quantite_offerte' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function storeSectionnable(Request $request){
        //
        $sectionnables = Sectionnable::whereIn('section_id', Section::where('commande_id', $request['document']['commande_id'])->pluck('id'))->get()->toArray();
        $array = array_filter($sectionnables, function($sectionnable) use ($request){
            return $sectionnable['sectionnable_id'] === $request['product']['id'];
        });
        if( isset($array) ){
            //
            $section = Section::where([ 'commande_id' => $request['document']['commande_id'], 'nom' => '***Retard***' ])->first();
            //
            if($section){
                // Crée le Sectionnable
                $sectionnable = Sectionnable::create([
                    'section_id' => $section->id,
                    'sectionnable_id' => $request['product']['id'],
                    'sectionnable_type' => 'App\Product',
                    'quantite' => $request['product']['quantite'],
                    'created_at' => now(),
                    'updated_at' => now(),
                    'conflit' => 0
                ]);
                // Insère le sectionnable au bon de commande
                DB::table('demande_sectionnable')->insert([
                    'sectionnable_id' => $sectionnable->id,
                    'demande_id' => $request['document']['id'],
                    'quantite_offerte' => $request['product']['quantite'],
                    'offre' => 0,
                    'traduction' => '',
                    'differente_offre' => 0,
                    'reference_differente_offre' => '',
                    'checked' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                $section = Section::create([
                    'commande_id' => $request['document']['commande_id'],
                    'nom' => '***Retard***',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                // Crée le Sectionnable
                $sectionnable = Sectionnable::create([
                    'section_id' => $section->id,
                    'sectionnable_id' => $request['product']['id'],
                    'sectionnable_type' => 'App\Product',
                    'quantite' => $request['product']['quantite'],
                    'created_at' => now(),
                    'updated_at' => now(),
                    'conflit' => 0
                ]);
                // Insère le sectionnable au bon de commande
                DB::table('demande_sectionnable')->insert([
                    'sectionnable_id' => $sectionnable->id,
                    'demande_id' => $request['document']['id'],
                    'quantite_offerte' => $request['product']['quantite'],
                    'offre' => 0,
                    'traduction' => '',
                    'differente_offre' => 0,
                    'reference_differente_offre' => '',
                    'checked' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

        } else  {
            DB::table('demande_sectionnable')->insert([
                'sectionnable_id' => $array['sectionnable_id'],
                'demande_id' => $request['document']['id'],
                'quantite' => $request['product']['quantite'],
                'offre' => 0,
                'traduction' => '',
                'traduction' => '',
                'differente_offre' => 0,
                'reference_differente_offre' => '',
                'checked' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return $sectionnable;
    }


    public function export(Demande $demande)
    {
        return Excel::download(new DemandeExport($demande->id), 'RFQ ' . $demande->nom . ' '  . $demande->id . '-10-2020' . '.xlsx');
    }

    public function import(Request $request){
        // return $request->file('files');
        foreach($request->file('files') as $file){
            Excel::import(new DemandeImport, $file);
        }
    }
    public function updateProduct(Demande $demande, Request $request) {

        DB::table('demande_sectionnable')->where('id', $request['pivot']['id'])->update([
            'offre' => $request['pivot']['offre'],
            'quantite_offerte' => $request['pivot']['quantite_offerte'],
            'updated_at' => now()
        ]);

    }
    public function updateSectionnable(Request $request){
        $translation = '';

        if(isset($request['product']['handle']['translation'])){
            $translation = $request['product']['handle']['translation'];
        }
        if(isset($request['product']['handle']['display1']) && isset($request['product'])){
            $translation .=  ' / ' . $request['product'][$request['product']['handle']['display1']];
        }
        if(isset($request['product']['handle']['display2']) && isset($request['product'])){
            $translation .=  ' / ' . $request['product'][$request['product']['handle']['display2']];
        }
        if(isset($request['product']['handle']['display3']) && isset($request['product'])){
            $translation .=  ' / ' . $request['product'][$request['product']['handle']['display3']];
        }


        DB::table('demande_sectionnable')->where('id', $request['pivot']['id'])->update([
            'traduction' => $translation
        ]);
    }

    public function updateTraduction(Request $request){
        DB::table('demande_sectionnable')->where('id', $request['pivot']['id'])->update([
            'traduction' => $request['pivot']['traduction']
        ]);
    }

    public function patchSectionnable(Request $request){
        DB::table('demande_sectionnable')->where('id', $request['id'])->update([
            $request['field'] => $request['value']
        ]);
        return 1;
    }
}
