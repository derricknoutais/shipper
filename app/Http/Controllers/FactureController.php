<?php

namespace App\Http\Controllers;

use DB;
use App\Facture;
use App\Product;
use App\Commande;
use App\BonCommande;
use App\Section;
use App\Sectionnable;
use Illuminate\Http\Request;

class FactureController extends Controller
{

    public function index(Commande $commande)
    {
        $commande->loadMissing(['bonsCommandes', 'bonsCommandes.sectionnables', 'bonsCommandes.sectionnables.product', 'factures', 'factures.sectionnables']);


        return view('facture.index', compact('commande'));
    }

    public function show(Facture $facture)
    {
        $facture->loadMissing('sectionnables', 'sectionnables.product', 'commande');
        // return  BonCommande::with('fournisseur')->get();
        $products = Product::all();
        return view('facture.show', compact('facture', 'products'));
    }

    public function updateSectionnable($sectionnable, Request $request){
        DB::table('facture_sectionnable')->where('id', $sectionnable)->update([
            'quantite' => $request['pivot']['quantite'],
            'prix_achat' => $request['pivot']['prix_achat']
        ]);
    }

    public function destroySectionnable($sectionnable){
        // return $sectionnable;
        DB::table('facture_sectionnable')->where('id', $sectionnable)->delete();
    }

    public function storeSectionnable(Request $request){
        //
        $section = Section::where([ 'commande_id' => $request['bc']['commande_id'], 'nom' => '***Retard***' ])->first();

        if($section){
            // Crée le Sectionnable
            if( ! ($sectionnable =  Sectionnable::where(['sectionnable_id' => $request['product']['id'],'section_id' => $section->id ])->first() ) ){
                $sectionnable = Sectionnable::create([
                    'section_id' => $section->id,
                    'sectionnable_id' => $request['product']['id'],
                    'sectionnable_type' => 'App\Product',
                    'quantite' => $request['product']['quantite'],
                    'created_at' => now(),
                    'updated_at' => now(),
                    'conflit' => 0
                ]);
            }
            // Insère le sectionnable au bon de commande

            DB::table('facture_sectionnable')->insert([
                'sectionnable_id' => $sectionnable->id,
                'facture_id' => $request['bc']['id'],
                'quantite' => $request['product']['quantite'],
                'prix_achat' => $request['product']['prix_achat'] * 165,
                'created_at' => now(),
                'updated_at' => now()
            ]);


        } else {

            $section = Section::create([
                'commande_id' => $request['bc']['commande_id'],
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
            DB::table('facture_sectionnable')->insert([
                'sectionnable_id' => $sectionnable->id,
                'facture_id' => $request['bc']['id'],
                'quantite' => $request['product']['quantite'],
                'prix_achat' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);

        }

        return $sectionnable;
    }
}
