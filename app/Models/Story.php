<?php

namespace App\Models;

use App\Interfaces\Likeable;
use App\Interfaces\Ownable;
use App\Interfaces\ShortUuid;
use App\Models\Traits\UsesUuid;
use App\Models\Traits\UsesShortUuid;
use Illuminate\Support\Facades\Auth;
use App\Models\Traits\LikeableTraits;
use App\Models\Traits\OwnableTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Story extends Model implements Likeable, Ownable
{
    use UsesUuid, HasFactory, LikeableTraits, SoftDeletes, OwnableTraits;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

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

    public function getOwner(): ?Collection
    {
        return new Collection([ $this->timeline->user ]);
    }
}
