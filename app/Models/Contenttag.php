<?php
namespace App\Models;

use DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use App\Models\Traits\UsesUuid;

class Contenttag extends Model
{
    use UsesUuid;

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
