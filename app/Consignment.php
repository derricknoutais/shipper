<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Consignment extends Model
{
    protected $guarded = [];
    protected $casts = ['id' => 'string'];
    public $table = 'consignments';
}
