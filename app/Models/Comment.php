<?php

namespace App\Models;

use App\Models\Post;
use App\Models\User;
use App\Interfaces\Ownable;
use App\Interfaces\Likeable;
use App\Interfaces\ShortUuid;
use App\Interfaces\Commentable;
use App\Models\Traits\UsesUuid;
use Illuminate\Support\Collection;
use Cviebrock\EloquentSluggable\Sluggable;
use App\Models\Traits\OwnableTraits;
use App\Models\Traits\UsesShortUuid;
use App\Models\Traits\LikeableTraits;
use App\Models\Traits\CommentableTraits;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model implements Likeable, Commentable, Ownable
{
    use UsesUuid, SoftDeletes, LikeableTraits, CommentableTraits, OwnableTraits, Sluggable;

    //protected $dates = ['deleted_at'];
    protected $guarded = [ 'id', 'created_at', 'updated_at' ];
    protected $casts = [ 'cattrs' => 'array', 'meta' => 'array', ];
    protected $hidden = [ 'deleted_at' ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => [ 'description' ]
            ]
        ];
    }

    public function getOwner(): ?Collection
    {
        return new Collection([ $this->user ]);
    }

    /* ------------------------------ Relations ----------------------------- */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function commentable() {
        return $this->morphTo();
    }

    public function replies()
    {
        // return $this->comments();
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function parent()
    {
        // return $this->belongsTo(Comment::class, 'commentable_id');
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function post()
    {
        //return $this->belongsTo(Post::class, 'commentable_id')->where('commentable_type', $this->getMorphString(Post::class));
        return $this->belongsTo(Post::class, 'commentable_id'); // %FIXME...the above doesn't work
    }

    /* ---------------------------- End Relations --------------------------- */

    public function getAttributeLikesCount()
    {
        return $this->likes->count();
    }

    public function getAttributeRepliesCount()
    {
        return $this->replies->count();
    }

    /**
     * Is this comment a reply to another comment
     */
    public function isReply(): bool
    {
        // return $this->commentable_type === $this->getMorphString(Comment::class);
        return isset($this->parent_id);
    }

    /**
     * Set Information to return for particular view.
     *
     * @param  string  $view - The view to set for
     * @param  [number|string]  $replyLevel - Number of levels of replies to populate. Set to `'all'` to force populate
     *      all levels
     */
    public function prepFor($view = null, $replyLevel = 1) {
        switch($view) {
            case 'post': default:
                $this->setVisible([
                    'id',
                    'description',
                    'user',
                    'likes_count',
                    'likesCount',
                    'replies_count',
                    'repliesCount',
                    'replies',
                    'created_at',
                    'updated_at',
                ]);
                $this->user->setVisible([ 'id', 'username', 'name', 'avatar', ]);
                if (
                    $this->user->avatar instanceof Mediafile
                ) {
                    $this->user->avatar->setVisible([ 'slug', 'filepath', 'name', ]);
                }
                if ( $replyLevel > 0 || $replyLevel === 'all' ) {
                    // $this->replies = Comment::with(['user'])->withCount('likes')->withCount('replies')->where('commentable_id', $this->id)->get();
                    $this->replies = Comment::with(['user'])->withCount('likes')->withCount('replies')->where('parent_id', $this->id)->get();
                    foreach($this->replies as &$reply) {
                        $reply->prepFor($view, ($replyLevel === 'all') ? $replyLevel : $replyLevel - 1);
                    }
                }
            break;
        }
        return $this;
    }

}
