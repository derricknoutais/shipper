<?php

namespace App\Jobs;

use DB;
use App\BonCommande;
use App\Demande;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenererBons implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $commande;
    protected $low;
    protected $high;
    public $timeout = 600;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($commande, $low, $high)
    {
        $this->commande = $commande;
        $this->low = $low;
        $this->high = $high;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $all = array();
        // Pour Chaque Demande d'Offre de Cette Commande...
        for (
            $i = $this->low; $i < $this->high; $i++
        ) {

            // Pour Chaque Sectionnable de Chaque Demande d'offre
            foreach($this->commande->demandes[$i]->sectionnables as $sectionnable){

                $this->commande->load('demandes', 'bonsCommandes');
                $this->commande->load('demandes.sectionnables');

                //  Si le produit n'a pas encore été checké
                if( $sectionnable->pivot->checked  == 0)
                {
                    unset($toCompare);
                    $toCompare = array();

                    // Ajoute le 1er produit dans notre liste à comparer
                    array_push($toCompare, $sectionnable);

                    // Pour toutes les autres demandes
                    for ($j=0; $j < sizeof($this->commande->demandes); $j++)
                    {
                        // Tant que ce n'est pas la meme demande dans laquelle est le produit
                        if($j != $i){
                            foreach($this->commande->demandes[$j]->sectionnables as $sectionnable_comparatif){
                                // Si le produit est identique au premier produit
                                if($sectionnable->sectionnable_id == $sectionnable_comparatif->sectionnable_id ){
                                    // Ajoute le à notre liste de produits a comparer
                                    array_push($toCompare, $sectionnable_comparatif);
                                }
                            }
                        }
                    }


                    // Classe les produits dans notre liste à comparer du moins cher au plus
                    usort($toCompare, function( $a, $b) {
                        // Pour deux produits au prix identiques
                        if($a->pivot->offre == $b->pivot->offre ){
                            $a->pivot->checked = -1;
                            $b->pivot->checked = -1;
                            DB::table('demande_sectionnable')
                                ->whereIn('id', [$b->pivot->id, $a->pivot->id])
                                ->update([
                                    'checked' => -1
                                ]);
                            DB::table('sectionnables')->where([ 'section_id' => $a->section_id, 'sectionnable_id' => $a->sectionnable_id ])->update([
                                'conflit' => 1
                            ]);
                        // Pour deux produits de prix inférieurs
                        } else {
                            $a->pivot->checked = 1;
                            $b->pivot->checked = 1;
                            DB::table('demande_sectionnable')
                                ->whereIn('id', [$b->pivot->id, $a->pivot->id])
                                ->update([
                                    'checked' => 1
                                ]);
                        }
                        return $a['pivot']['offre'] <=> $b['pivot']['offre'];
                    });

                    foreach ($toCompare as $comp) {
                        if($comp->pivot->offre <= 0 || $comp->pivot->differente_offre){
                            DB::table('demande_sectionnable')
                            ->where('id', $comp->pivot->id)
                            ->update([
                                'checked' => -1
                            ]);
                            $comp->pivot->checked = -1;
                            DB::table('sectionnables')->where([ 'section_id' => $comp->section_id, 'sectionnable_id' => $comp->sectionnable_id ])->update([
                                'conflit' => 1
                            ]);
                        }
                    }
                    // return $toCompare;
                    $qte_recevable = $toCompare[0]->quantite;
                    $x = 0;
                    while ( $qte_recevable > 0 ) {
                        // Si la quantité a recevoir  est supérieure a la quantite offerte par le fournisseur x

                        if( ( $qte_recevable - $toCompare[$x]->pivot->quantite_offerte )  > 0 ){

                            // Prennons tout ce que le fournisseur nous offre
                            $toCompare[$x]->quantite_prise = $toCompare[$x]->pivot->quantite_offerte;

                            // Puis passons au fournisseur suivant si il en reste
                            if( ($x + 1) < sizeof($toCompare) ){
                                $x++;
                            } else {
                                // Sinon on stoppe
                                break;
                            }
                        }
                        // Si la quantité a recevoir est inférieure a la quantite offerte par le fournisseur x
                        else {
                            // On prend seulement la quantité restante et on stoppe l'exectution
                            $toCompare[$x]->quantite_prise = $qte_recevable ;
                            break;
                        }

                        // On soustrait la quantité prise chez le fournisseur x de notre total
                        if($x > 0){
                            $qte_recevable = $qte_recevable - $toCompare[$x-1]->quantite_prise;
                        } else {
                            $qte_recevable = $qte_recevable - $toCompare[$x]->quantite_prise;
                        }

                        // Stoppe la boucle si nous sommes au bout de notre tableau de fournisseur
                        if( $x >= sizeof($toCompare)  ) {
                            break;
                        }

                    }
                    // return $x;


                    for ($y=0; $y <= $x ; $y++) {
                        if( $toCompare[$y]->pivot->checked !== -1 ){
                            if( $bc = BonCommande::where('demande_id' , $toCompare[$y]->pivot->demande_id )->first() )
                            {
                                DB::table('bon_commande_sectionnable')->insert([
                                    'bon_commande_id' => $bc->id,
                                    'sectionnable_id' => $toCompare[$y]->id,
                                    'quantite' => $toCompare[$y]->quantite_prise,
                                    'prix_achat' => $toCompare[$y]->pivot->offre
                                ]);
                            }
                            else
                            {
                                $demande = Demande::find($toCompare[$y]->pivot->demande_id);
                                $bc = BonCommande::create([
                                    'commande_id' => $this->commande->id,
                                    'nom' => 'Bon Commande ' . $demande->nom ,
                                    'demande_id' => $toCompare[$y]->pivot->demande_id
                                ]);
                                DB::table('bon_commande_sectionnable')->insert([
                                    'bon_commande_id' => $bc->id,
                                    'sectionnable_id' => $toCompare[$y]->id,
                                    'quantite' => $toCompare[$y]->quantite_prise,
                                    'prix_achat' => $toCompare[$y]->pivot->offre
                                ]);
                            }
                            DB::table('demande_sectionnable')
                            ->where('id', $toCompare[$y]->pivot->id)
                            ->update([
                                'checked' => 1
                            ]);
                            $toCompare[$y]->pivot->checked = 1;
                        }
                    }
                }
            }
        }
        return 'Done';
    }
}
