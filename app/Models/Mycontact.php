<?php 
namespace App\Models;

use App\Interfaces\Ownable;
use App\Models\Traits\UsesUuid;
use Illuminate\Support\Collection;
use App\Models\Traits\OwnableTraits;

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
        OwnableTraits;

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

