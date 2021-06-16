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

    //------------------------------------------------------------------------//
    //                            Model Properties                            //
    // ---------------------------------------------------------------------- //
    #region Model Properties

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    #endregion Model Properties

    //------------------------------------------------------------------------//
    //                       Accessors/Mutators | Casts                       //
    //------------------------------------------------------------------------//
    #region Casts

    protected $casts = [
        'cattrs' => 'array',
        'meta'   => 'array',
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
    //                                Functions                               //
    //------------------------------------------------------------------------//
    #region Functions


    /**
     * Adds contacts for each user in collection to each user in collection if
     * they don't already have that user as a contact.
     *
     * Warning: this is O(n^2), don't hold up web requests with big collections.
     *
     * @param   Collection  $users  Collection of users
     * @param   User        $forUser  Optional if you only want to add contacts to a specific user
     * @return  Collection  Collection of mycontacts
     */
    public static function addContacts(Collection $users, User $forUser = null)
    {
        $mycontacts = new Collection();

        if (isset($forUser)) {
            $users->each(function ($user) use (&$mycontacts, $forUser) {
                $mycontacts->push(Mycontact::firstOrCreate([
                    'owner_id' => $forUser->getKey(),
                    'contact_id' => $user->getKey(),
                ]));
            });
        } else {
            $users->each(function ($owner) use (&$mycontacts, $users) {
                $users->each(function ($contact) use (&$mycontacts, $owner) {
                    if ($owner->getKey() !== $contact->getKey()) {
                        $mycontacts->push(Mycontact::firstOrCreate([
                            'owner_id' => $owner->getKey(),
                            'contact_id' => $contact->getKey(),
                        ]));
                    }
                });
            });
        }

        return $mycontacts;
    }

    #endregion Functions
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
            'id'       => $this->getKey(),
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

