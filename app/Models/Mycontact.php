<?php 
namespace App\Models;

use App\Interfaces\Ownable;
use App\Models\Traits\UsesUuid;
use Illuminate\Support\Collection;
use App\Models\Traits\OwnableTraits;
use Laravel\Scout\Searchable;

/**
 * @property string $id         | uuid
 * @property string $owner_id   | uuid
 * @property string $contact_id | uuid
 * @property string $alias      | varchar(255)
 * @property array  $cattrs     | custom attributes
 * @property array  $meta       | metadata
 *
 * Relations
 * @property User $contact | contact user
 * @property User $owner   | owner of this mycontact
 *
 * @package App\Models
 */
class Mycontact extends Model implements Ownable
{
    use UsesUuid,
        OwnableTraits,
        Searchable;

    protected $guarded = [ 'created_at', 'updated_at' ];

    //------------------------------------------------------------------------//
    //                       Accessors/Mutators | Casts                       //
    //------------------------------------------------------------------------//
    #region Casts

    protected $casts = [
        'cattrs'    => 'array',
        'meta'      => 'array',
    ];

    #endregion Casts
    //------------------------------------------------------------------------//

    //------------------------------------------------------------------------//
    //                               Searchable                               //
    //------------------------------------------------------------------------//
    #region Searchable
    /**
     * Name of the search index associated with this model
     * @return string
     */
    public function searchableAs()
    {
        return "mycontacts_index";
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
        return [
            'name'     => $this->contact->timeline->name,
            'slug'     => $this->contact->timeline->slug,
            'username' => $this->contact->username,
            'alias'    => $this->alias,
            'owner_id' => $this->owner_id,
        ];
    }

    #endregion Searchable
    //------------------------------------------------------------------------//

    //------------------------------------------------------------------------//
    //                              Relationships                             //
    //------------------------------------------------------------------------//
    #region Relationships
    /*
    public function shareable() {
        return $this->morphTo();
    }
     */

    public function contact()
    {
        return $this->belongsTo(User::class, 'contact_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    #endregion Relationships
    //------------------------------------------------------------------------//

    //------------------------------------------------------------------------//
    //                                Ownable                                 //
    //------------------------------------------------------------------------//
    #region Ownable

    public function getOwner(): ?Collection
    {
        return new Collection([ $this->owner ]);
    }
    #endregion Ownable
    //------------------------------------------------------------------------//

}

