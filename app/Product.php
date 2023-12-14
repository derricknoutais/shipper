<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    // protected $casts = ['id' => 'string'];
    public $incrementing = false;
    public $timestamps = false;

    public function commandes()
    {
        return $this->morphToMany('App\Commande', 'commandable');
    }

    public function fournisseurs()
    {
        return $this->belongsToMany('App\Fournisseur', 'product_fournisseur', 'product_id', 'fournisseur_id');
    }
    public function handle()
    {
        return $this->belongsTo('App\Handle', 'handle_name', 'name');
    }
}
