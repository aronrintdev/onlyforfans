<?php
namespace App;

use Eloquent as Model;

class Story extends Model
{
    protected $guarded = ['id','created_at','updated_at'];

    public function getCattrsAttribute($value) {
        return empty($value) ? [] : json_decode($value,true);
    }

    public function setCattrsAttribute($value) {
        $this->attributes['cattrs'] = json_encode($value);
    }

}
