<?php
namespace App\Models;

use DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use App\Models\Traits\UsesUuid;
use App\Enums\ContenttagAccessLevelEnum;

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
        return $this->morphedByMany(Mediafile::class, 'contenttaggable');
    }

    public function posts() {
        return $this->morphedByMany(Post::class, 'contenttaggable');
    }

    public function stories() {
        return $this->morphedByMany(Story::class, 'contenttaggable');
    }

    public function vaultfolders() {
        return $this->morphedByMany(Vaultfolder::class, 'contenttaggable');
    }

}
