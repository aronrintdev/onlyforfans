<?php
namespace App\Models;

use App\Interfaces\Likeable;
use App\Interfaces\Ownable;
use App\Models\Traits\UsesUuid;
use Illuminate\Support\Facades\Auth;
use App\Models\Traits\LikeableTraits;
use Cviebrock\EloquentSluggable\Sluggable;
use App\Models\Traits\OwnableTraits;
use App\Models\Traits\SluggableTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Laravel\Scout\Searchable;

class Story extends Model implements Likeable, Ownable
{
    use UsesUuid;
    use HasFactory;
    use LikeableTraits;
    use SoftDeletes;
    use Sluggable;
    use SluggableTraits;
    use OwnableTraits;
    use Searchable;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function sluggable(): array
    {
        return ['slug' => [
            'source' => [ 'sluggableContent' ],
        ]];
    }

    public function getSluggableContentAttribute(): string
    {
        return $this->timeline->name . ' ' . 'story';
    }

    //--------------------------------------------
    // Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'content' => 'array',
        'cattrs' => 'array',
        'meta' => 'array',
    ];

    public function getIsLikedByMeAttribute($value)
    {
        $sessionUser = Auth::user();
        return $this->likes->contains($sessionUser->id);
    }

    //--------------------------------------------
    // Relationships
    //--------------------------------------------

    public function mediafiles()
    {
        return $this->morphMany(Mediafile::class, 'resource');
    }

    public function timeline()
    {
        return $this->belongsTo('App\Models\Timeline');
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
        return "stories_index";
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
            'name'    => $this->timeline->name,
            'slug'    => $this->slug,
            'content' => $this->content,
            'id'      => $this->getKey(),
        ];
    }

    #endregion Searchable
    /* ---------------------------------------------------------------------- */

    //--------------------------------------------
    // Overrides
    //--------------------------------------------

    /*
    public static function create(array $attrs=[])
    {
        $model = static::query()->create($attrs);
        // ...
        return $model;
    }
     */

    public function getOwner(): ?Collection
    {
        return new Collection([ $this->timeline->user ]);
    }

    public function getPrimaryOwner(): User
    {
        return $this->timeline->user;
    }
}
