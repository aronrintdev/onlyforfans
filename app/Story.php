<?php
namespace App;

use Eloquent as Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Story extends Model
{
    use HasFactory;

    protected $guarded = ['id','created_at','updated_at'];

    //--------------------------------------------
    // Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'cattrs' => 'array',
    ];

    public function getIsLikedByMeAttribute($value) {
        $sessionUser = Auth::user();
        return $this->likes->contains($sessionUser->id);
    }

    //--------------------------------------------
    // Relationships
    //--------------------------------------------

    public function likes() {
        return $this->morphToMany('App\User', 'likeable', 'likeables', 'likeable_id', 'likee_id')->withTimestamps();
    }

    public function mediafiles() {
        return $this->morphMany('App\Mediafile', 'resource');
    }

    public function timeline() {
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
