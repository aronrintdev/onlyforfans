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

class Story extends Model implements Likeable, Ownable
{
    use UsesUuid;
    use HasFactory;
    use LikeableTraits;
    use SoftDeletes;
    use Sluggable;
    use SluggableTraits;
    use OwnableTraits;

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
}
