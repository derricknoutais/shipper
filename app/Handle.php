<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Handle extends Model
{
    protected $guarded = [];
    public function brands()
    {
        return $this->belongsToMany('App\Brand');
    }
}
