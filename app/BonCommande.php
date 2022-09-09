<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BonCommande extends Model
{
    protected $guarded = [];

    public function sectionnables()
    {
        return $this->belongsToMany('App\Sectionnable', 'bon_commande_sectionnable', 'bon_commande_id', 'sectionnable_id')->withPivot('id', 'prix_achat', 'quantite', 'traduction');
    }
    public function commande()
    {
        return $this->belongsTo('App\Commande');
    }
    public function fournisseur()
    {
        return $this->belongsTo('App\Fournisseur');
    }
}
