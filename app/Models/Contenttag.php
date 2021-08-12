<?php
namespace App\Models;

use DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class Contenttag extends Model
{
    protected $guarded = [ 'id', 'created_at', 'updated_at', ];

    //--------------------------------------------
    // Accessors/Mutators | Casts | Attributes
    //--------------------------------------------

    //--------------------------------------------
    // Relationships
    //--------------------------------------------

    public function mediafiles() {
        return $this->morphByMany(Mediafile::class, 'contenttaggable');
    }

}
