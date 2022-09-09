<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Commande extends Model
{
    use LogsActivity;
    protected $guarded = [];

    public function products()
    {
        return $this->morphedByMany('App\Product', 'commandable');
    }

    public function sections()
    {
        return $this->hasMany('App\Section');
    }

    public function templates()
    {
        return $this->morphedByMany('App\Template', 'commandable');
    }

    public function reorderpoint()
    {
        return $this->morphedByMany('App\Reorderpoint', 'commandable');
    }
    public function demandes()
    {
        return $this->hasMany('App\Demande');
    }
    public function bonsCommandes()
    {
        return $this->hasMany('App\BonCommande');
    }
    public function factures()
    {
        return $this->hasMany('App\Facture');
    }
    public function total(){
        // $total = 0;
        // $this->loadMissing('bonsCommandes');
        // if($this->bons_commandes){
        //     foreach($this->bons_commandes as $bon ) {
        //         if(sizeof($bon->sectionnables) > 0 ){
        //             foreach ($bon->sectionnables as $sectionnable ) {
        //                 $total += $sectionnable->pivot->prix_achat * $sectionnable->pivot->quantite;
        //             }
        //         }
        //     }
        // }

        // return $total;
    }

}
