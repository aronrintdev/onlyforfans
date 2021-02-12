<?php

namespace App\Models;

use App\Interfaces\Likeable;
use App\Interfaces\ShortUuid;
use App\Models\Traits\UsesUuid;
use App\Models\Traits\UsesShortUuid;
use Illuminate\Support\Facades\Auth;
use App\Models\Traits\LikeableTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Story extends Model implements Likeable
{
    use UsesUuid;
    use HasFactory;
    use LikeableTraits;
    use SoftDeletes;

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
        return $this->morphMany('App\Mediafile', 'resource');
    }

    public function timeline()
    {
        return $this->belongsTo('App\Timeline');
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
