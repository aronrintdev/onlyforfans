<?php
namespace App\Models;

use DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use App\Models\Traits\UsesUuid;
use App\Enums\ContenttagAccessLevelEnum;
use Laravel\Scout\Searchable;

class Contenttag extends Model
{
    use UsesUuid, Searchable;

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

    public function vaultfolders() {
        return $this->morphedByMany(Vaultfolder::class, 'contenttaggable');
    }

    public function contenttaggable()
    {
        return $this->morphTo();
    }


    /* ---------------------------------------------------------------------- */
    /*                               Searchable                               */
    /* ---------------------------------------------------------------------- */
    #region Searchable

    /**
     * Name of the search index associated with this model
     * @return string
     */
    public function searchableAs()
    {
        return "contenttags_index";
    }

    /**
     * Get value used to index the model
     * @return mixed
     */
    public function getScoutKey()
    {
        return $this->getKey();
    }

    /**
     * Get key name used to index the model
     * @return string
     */
    public function getScoutKeyName()
    {
        return 'id';
    }

    /**
     * What model information gets stored in the search index
     * @return array
     */
    public function toSearchableArray()
    {
        // $resources = $this->contenttaggable->get();
        // foreach( $resources as $resource) {
            return [
                'ctag' => $this->ctag,
                'id' => $this->getKey(),
            ];
        // }
    }

    #endregion Searchable
    /* ---------------------------------------------------------------------- */

}
