<?php 
namespace App\Models;

use Illuminate\Support\Collection;

class Contenttaggable extends Model 
{
    //use UsesUuid;

    protected $guarded = [ 'created_at', 'updated_at' ];

    public function contenttaggable() {
        return $this->morphTo();
    }

    /*
    public function contenttag() { 
        return $this->belongsTo(Contenttag::class, 'contenttag_id'); 
    }
     */

}

