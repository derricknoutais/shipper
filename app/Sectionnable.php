<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Sectionnable extends Pivot
{
    protected $table= 'sectionnables';
    protected  $primaryKey = 'id';
    public $incrementing = true;

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo('App\Product', 'sectionnable_id');
    }
    public function article()
    {
        return $this->belongsTo('App\Article', 'sectionnable_id');
    }
    public function section()
    {
        return $this->belongsTo('App\Section', 'section_id');
    }
    public function demandes()
    {
        return $this->belongsToMany('App\Demande', 'demande_sectionnable', 'sectionnable_id', 'demande_id')->withPivot('id', 'offre', 'quantite_offerte', 'differente_offre', 'reference_differente_offre');
    }

    public function bon_commande(){
        return $this->belongsToMany('App\BonCommande', 'bon_commande_sectionnable', 'sectionnable_id')->withPivot('id');
    }
    public function factures(){
        return $this->belongsToMany('App\Facture', 'facture_sectionnable', 'sectionnable_id')->withPivot('id');
    }

}
