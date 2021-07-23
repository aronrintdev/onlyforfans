<?php
namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

use Cviebrock\EloquentSluggable\Sluggable;
use Laravel\Scout\Searchable;

use App\Interfaces\Likeable;
use App\Interfaces\Ownable;

use App\Models\Traits\UsesUuid;
use App\Models\Traits\LikeableTraits;
use App\Models\Traits\OwnableTraits;
use App\Models\Traits\SluggableTraits;

class Story extends Model implements Likeable, Ownable
{
    use UsesUuid, HasFactory, LikeableTraits, SoftDeletes, Sluggable, SluggableTraits, OwnableTraits, Searchable;

    protected $guarded = [ 'id', 'created_at', 'updated_at', ];

    //--------------------------------------------
    // Boot
    //--------------------------------------------

    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            //$this->insertStoryqueues();
            // As this is a new story, we don't need to worry about 24 hour filtering...
            $followers = $model->timeline->followers ?? collect();
            $attrs = [];
            $followers->each( function($f) use(&$model, &$attrs) {
                $attrs[] = [
                    'viewer_id' => $f->id,
                    'story_id' => $model->id,
                    'timeline_id' => $model->timeline->id,
                    'created_at' => $model->created_at,
                    'updated_at' => $model->created_at,
                ];
            });

            // create [storyqueues] records
            DB::table('storyqueues')->insert($attrs);
        });

        static::deleting(function ($model) {
            // Delete any storyqueue relations for this story...
            foreach ($model->storyqueues as $sq) {
                $sq->delete();
            }
        });
    }

    //--------------------------------------------
    // Relationships
    //--------------------------------------------

    public function mediafiles() {
        return $this->morphMany(Mediafile::class, 'resource');
    }

    public function timeline() {
        return $this->belongsTo(Timeline::class);
    }

    public function storyqueues() {
        return $this->hasMany(Storyqueue::class);
    }

    //--------------------------------------------
    // Sluggable
    //--------------------------------------------

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
