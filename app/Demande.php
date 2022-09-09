<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Demande extends Model
{
    protected $guarded = [];

    public function commande()
    {
        return $this->belongsTo('App\Commande');
    }

    public function fournisseur(){
        return $this->belongsTo('App\Fournisseur');
    }

    public function sectionnables()
    {
        return $this->belongsToMany('App\Sectionnable', 'demande_sectionnable', 'demande_id', 'sectionnable_id')->withPivot('offre', 'id', 'checked', 'quantite_offerte', 'traduction', 'differente_offre', 'reference_differente_offre');
    }

    public function bonCommande()
    {
        return $this->belongsTo('App\BonCommande', 'bon_commande_id');
    }
}
