<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Subscription extends Model
{
    public $timestamps = false;


    protected $guarded = ['id','created_at','updated_at'];

    //--------------------------------------------
    // Relationships
    //--------------------------------------------

    public function ledgersales() {
        return $this->morphMany('App\Fanledger', 'purchaseable');
    }
}
