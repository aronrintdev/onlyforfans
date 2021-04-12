<?php 
namespace App\Models;

//use App\Interfaces\ShortUuid;
//use App\Models\Traits\UsesUuid;
use Illuminate\Support\Collection;
//use App\Models\Traits\UsesShortUuid;

class Likeable extends Model 
{
    //use UsesUuid;

    protected $guarded = [ 'created_at', 'updated_at' ];
    //protected $casts = [ 'cattrs' => 'array', 'meta' => 'array', ];

    public function likeable() {
        return $this->morphTo();
    }
    /*
    public function likedposts() { 
        return $this->morphedByMany(Post::class, 'likeable', 'likeables', 'sharee_id');
    }
    public function likedposts()
    { 
        return $this->hasMany(Post::class, 'user_id');
    }
     */
}

