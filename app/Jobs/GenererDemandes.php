<?php

namespace App\Jobs;

use App\Demande;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenererDemandes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $commande;
    protected $response;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($commande)
    {
        $this->commande = $commande;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->commande->loadMissing('sections', 'sections.sectionnables', 'sections.sectionnables.product', 'sections.sectionnables.product.fournisseurs');
        /***
            Pour chaque section de la commande
            Exemple : Toyota C... / Avensis / Joints / Nouveaux Produits sont des sections
        */
        $i = 0;
        foreach($this->commande->sections as $section){
            /***
                Chaque Section a des sectionnables. Les sectionnables sont des éléments qui appartiennent à des sections
                Il existe 2 types de sectionnables : Les Produits Vend & Les Articles Demandées par les clients dans Fiche de Renseignement
                Donc Pour chaque sectionnable de chaque Section
            */

            foreach($section->sectionnables as $sectionnable){

                if($sectionnable->sectionnable_type === 'App\\Product'){
                    if(isset($sectionnable->product->fournisseurs)){

                        foreach($sectionnable->product->fournisseurs as $fournisseur){
                            if($demande = Demande::where( ['fournisseur_id' => $fournisseur->id, 'commande_id' => $this->commande->id])->first() ){
                                DB::table('demande_sectionnable')->insert([
                                    'sectionnable_id' => $sectionnable->id,
                                    'demande_id' => $demande->id,
                                    'offre' => 0,
                                    'quantite_offerte' => 0,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                                $i++;
                            } else {
                                $demande = Demande::create([
                                    'nom' => $fournisseur->nom,
                                    'commande_id' => $this->commande->id,
                                    'fournisseur_id' => $fournisseur->id,
									'created_at' => now(),
									'updated_at' => now()
                                ]);
                                DB::table('demande_sectionnable')->insert([
                                    'sectionnable_id' => $sectionnable->id,
                                    'demande_id' => $demande->id,
                                    'offre' => 0,
                                    'quantite_offerte' => 0,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                                $i++;
                            }
                        }
                    }
                }
            }
        }
        $this->response = $i;
    }
    public function getResponse()
    {
        return $this->response;
    }
}
