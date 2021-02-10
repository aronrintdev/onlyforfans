<?php

namespace App\Models;

use Eloquent as Model;
use App\Interfaces\Likeable;
use App\Models\Traits\LikeableTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Story extends Model implements Likeable
{
    use HasFactory;
    use LikeableTraits;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    //--------------------------------------------
    // Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'cattrs' => 'array',
    ];

    public function getIsLikedByMeAttribute($value)
    {
        $sessionUser = Auth::user();
        return $this->likes->contains($sessionUser->id);
    }

    //--------------------------------------------
    // Relationships
    //--------------------------------------------

    public function mediaFiles()
    {
        return $this->morphMany('App\MediaFile', 'resource');
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
