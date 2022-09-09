<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    protected $guarded = [];
    public function sectionnables()
    {
        return $this->belongsToMany('App\Sectionnable', 'facture_sectionnable', 'facture_id', 'sectionnable_id')->withPivot('id', 'prix_achat', 'quantite');
    }
    public function commande()
    {
        return $this->belongsTo('App\Commande');
    }
}
