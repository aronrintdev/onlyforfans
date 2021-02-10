<?php

namespace App\Models;

use App\Interfaces\Ownable;
use App\Interfaces\Likeable;
use App\Interfaces\ShortUuid;
use App\Interfaces\Commentable;
use App\Models\Traits\UsesUuid;
use App\Models\Traits\UsesShortUuid;
use App\Models\Traits\LikeableTraits;
use App\Models\Traits\CommentableTraits;
use App\Models\Traits\OwnableTraits;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Comment extends Model implements ShortUuid, Likeable, Commentable, Ownable
{
    use UsesUuid;
    use UsesShortUuid;
    use SoftDeletes;
    use LikeableTraits;
    use CommentableTraits;
    use OwnableTraits;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    //protected $dates = ['deleted_at'];

    protected $fillable = [
        'post_id',
        'description',
        'user_id',
        'parent_id'
    ];

    public function getOwner(): ?Collection
    {
        return new Collection([ $this->user ]);
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function commentable() {
        return $this->morphTo();
    }

    public function replies()
    {
        return $this->comments();
    }

    public function post()
    {
        return $this->belongsTo('App\Models\Post', 'commentable_id')
            ->where('commentable_type', $this->getMorphString('App\Models\Post'));
    }

}
