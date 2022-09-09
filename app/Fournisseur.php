<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    protected $guarded = [];

    public function bonsCommandes()
    {
        return $this->hasMany('App\BonCommande');
    }

    public function demandes()
    {
        return $this->hasMany('App\Demande');
    }

    public function products()
    {
        return $this->belongsToMany('App\Product', 'product_fournisseur');
    }


}
