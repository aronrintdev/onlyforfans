<?php
namespace App;

use Eloquent as Model;

class Story extends Model
{
    protected $guarded = ['id','created_at','updated_at'];

    //--------------------------------------------
    // Relationships
    //--------------------------------------------

    public function timeline() {
        return $this->belongsTo('App\Timeline');
    }

    public function mediafiles() {
        return $this->morphMany('App\Mediafile', 'resource');
    }

    //--------------------------------------------
    // Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'cattrs' => 'array',
    ];
}
