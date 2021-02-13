<?php

namespace App\Models;

use App\Interfaces\Likeable;
use App\Interfaces\ShortUuid;
use App\Models\Traits\UsesUuid;
use App\Models\Traits\UsesShortUuid;
use Illuminate\Support\Facades\Auth;
use App\Models\Traits\LikeableTraits;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Story extends Model implements Likeable
{
    use UsesUuid;
    use HasFactory;
    use LikeableTraits;
    use SoftDeletes;
    use Sluggable;

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
        return $this->morphMany('App\Models\Mediafile', 'resource');
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
}
