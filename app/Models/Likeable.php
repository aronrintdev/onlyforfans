<?php 
namespace App\Models;

use Illuminate\Support\Collection;
//use App\Interfaces\ShortUuid;
//use App\Models\Traits\UsesUuid;
//use App\Models\Traits\UsesShortUuid;

class Likeable extends Model 
{
    //use UsesUuid;

    protected $guarded = [ 'created_at', 'updated_at' ];
    //protected $casts = [ 'cattrs' => 'array', 'meta' => 'array', ];

    public function likeable() {
        return $this->morphTo();
    }

    public function liker()
    { 
        return $this->belongsTo(User::class, 'likee_id'); // %FIXME: mis-named column should be liker_id AF-269
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

