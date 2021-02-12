<?php

namespace App\Models;

use DB;
use Auth;

use App\Interfaces\Ownable;
use App\Interfaces\Blockable;
use App\Interfaces\ShortUuid;
use App\Models\Traits\UsesUuid;

use App\Interfaces\PaymentSendable;
use App\Models\Traits\UsesShortUuid;

use Spatie\Permission\Traits\HasRoles;
use Cmgmyr\Messenger\Traits\Messagable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements PaymentSendable, Blockable
{
    use Notifiable, HasRoles, HasFactory, Messagable, SoftDeletes, UsesUuid;

    protected $appends = [
        'name',
        'avatar',
        'cover',
        'about',
    ];

    protected $fillable = [
        'is_online',
        'last_logged',
    ];

    // protected $guarded = [];

    protected $hidden = [
        'email',
        'password',
        'remember_token',
        'verification_code',
    ];

    protected $dates = [
        'last_logged',
    ];

    //--------------------------------------------
    // Boot
    //--------------------------------------------
    public static function boot()
    {
        parent::boot();
        parent::boot();
        self::creating(function ($model) {
            $model->checkUsername();
        });
        self::updating(function ($model) {
            $model->checkUsername();
        });
        self::saving(function ($model) {
            $model->checkUsername();
        });

        static::created(function ($model) {
            $vault = Vault::create([
                'vname' => 'My Home Vault',
                'user_id' => $model->id,
            ]);
        });

        static::deleting(function ($model) {
            foreach ($model->vaults as $o) {
                $o->delete();
            }
        });
    }

    public function toArray()
    {
        $array = parent::toArray();
        $timeline = $this->timeline->toArray();
        foreach ($timeline as $key => $value) {
            if ($key != 'id') {
                $array[$key] = $value;
            }
        }
        $array['avatar'] = $this->avatar;
        return $array;
    }

    /**
     * Makes username a valid random username if it is null or empty.
     */
    public function checkUsername()
    {
        if (!isset($this->username) || $this->username === '') {
            $this->username = UsernameRule::createRandom();
        }
    }

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    /**
     * Mediafiles shared with me (??)
     */
    public function sharedMediafiles()
    {
        return $this->morphedByMany('App\Models\Mediafile', 'shareable', 'shareables', 'sharee_id')
            ->withTimestamps();
    }

    /**
     * Vaultfolders shared with me (??)
     */
    public function sharedVaultfolders()
    {
        return $this->morphedByMany('App\Models\Vaultfolder', 'shareable', 'shareables', 'sharee_id')
            ->withTimestamps();
    }
    public function ledgerSales()
    {
        return $this->hasMany('App\Models\Fanledger', 'seller_id');
    }
    public function ledgerPurchases()
    {
        return $this->hasMany('App\Models\Fanledger', 'purchaser_id');
    }

    /**
     * My timeline
     */
    public function timeline()
    {
        return $this->hasOne('App\Models\Timeline');
    }

    /**
     * timelines (users) I follow: premium *and* default subscribe (follow)
     */
    public function followedtimelines()
    {
        return $this->morphedByMany('App\Models\Timeline', 'shareable', 'shareables', 'sharee_id')
            ->withPivot('access_level', 'shareable_type', 'sharee_id')->withTimestamps();
    }

    public function likedPosts()
    {
        return $this->morphedByMany('App\Models\Post', 'likeable', 'likeables', 'user')
            ->withTimestamps();
    }

    /**
     * posts shared with me (by direct share or purchase on my part)
     */
    public function sharedposts()
    {
        return $this->morphedByMany('App\Models\Post', 'shareable', 'shareables', 'sharee_id')->withTimestamps();
    }

    /**
     * Users Notifications
     */
    public function notifications()
    {
        return $this->hasMany('App\Models\Notification')->with('notified_from');
    }

    /**
     * Vaults Owned by user
     */
    public function vaults()
    {
        return $this->hasMany('App\Models\Vault');
    }

    /**
     * Vault Folders owned by user
     */
    public function vaultfolders()
    {
        return $this->hasMany('App\Models\Vaultfolder');
    }

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    // https://stackoverflow.com/questions/30226496/how-to-cast-eloquent-pivot-parameters
    /* %PSG: could not get this to work, just do 'manually' in controller or other calling code
    protected $casts = [
        'sharedposts.pivot.custom_attributes' => 'array',
        'sharedposts.pivot.meta' => 'array',
    ];
     */

    public function getNameAttribute($value)
    {
        return $this->timeline->name;
    }

    public function getAvatarAttribute($value)
    {
        return $this->timeline->avatar
            ? $this->timeline->avatar
            : (object) ['filepath' => url('user/avatar/default-' . $this->gender . '-avatar.png')];
    }

    public function getCoverAttribute($value)
    {
        return $this->timeline->cover ? $this->timeline->cover : null;
    }

    public function getAboutAttribute($value)
    {
        return $this->timeline->about ? $this->timeline->about : null;
    }

    //this method is for displaying user avatar and default avatar from group in events feature
    public function getPictureAttribute($value)
    {
        return $this->timeline->avatar
            ? $this->timeline->avatar
            : url('group/avatar/default-group-avatar.png');
    }

    // ---

    public function getUserSettings($user_id)
    {
        return DB::table('user_settings')->where('user_id', $user_id)->first();
    }

    public function getUserListTypes($user_id)
    {
        return DB::table('user_list_types')->where('user_id', $user_id)->get();
    }

    public function deleteUserSettings($user_id)
    {
        return DB::table('user_settings')->where('user_id', $user_id)->delete();
    }

    public function getOthersSettings($username)
    {
        $timeline = Timeline::where('username', $username)->first();
        $user = self::where('timeline_id', $timeline->id)->first();
        $result = DB::table('user_settings')->where('user_id', $user->id)->first();

        return $result;
    }

    public function getUserPrivacySettings($loginId, $others_id)
    {
        $timeline_post_privacy = '';
        $timeline_post = '';
        $user_post = '';
        $result = '';

        $live_user_settings = $this->getUserSettings($others_id);

        if ($live_user_settings) {
            $timeline_post_privacy = $live_user_settings->timeline_post_privacy;
            $user_post_privacy = $live_user_settings->post_privacy;
        }

        //start $this if block is for timeline post privacy settings
        if ($loginId != $others_id) {
            if ($timeline_post_privacy != null && $timeline_post_privacy == 'only_follow') {
                $isFollower = $this->chkMyFollower($others_id, $loginId);
                if ($isFollower) {
                    $timeline_post = true;
                }
            } elseif ($timeline_post_privacy != null && $timeline_post_privacy == 'everyone') {
                $timeline_post = true;
            } elseif ($timeline_post_privacy != null && $timeline_post_privacy == 'nobody') {
                $timeline_post = false;
            }

            //start $this if block is for user post privacy settings
            if ($user_post_privacy != null && $user_post_privacy == 'only_follow') {
                $isFollower = $this->chkMyFollower($others_id, $loginId);
                if ($isFollower) {
                    $user_post = 'user';
                }
            } elseif ($user_post_privacy != null && $user_post_privacy == 'everyone') {
                $user_post = 'user';
            }
        } else {
            $timeline_post = true;
            $user_post = 'user';
        }
        //End
        $result = $timeline_post . '-' . $user_post;

        return $result;
    }

    public function settings()
    {
        $settings = DB::table('user_settings')->where('user_id', $this->id)->first();
        return $settings;
    }

    public function commentLikes()
    {
        return $this->morphedByMany('App\Models\Comment', 'likeable', 'likeables', 'user')
            ->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function deleteOthers()
    {
        // %PSG: Delete posts that aren't my own from my timeline
        $sessionUser = Auth::user();
        $otherPosts = $this->timeline->posts()->where('user_id', '!=', $sessionUser->id)->get();
        foreach ($otherPosts as $otherPost) {
            $otherPost->users_liked()->detach();
            //$otherPost->notifications_user()->detach();
            $otherPost->sharees()->detach();
            $otherPost->reports()->detach();
            $otherPost->users_tagged()->detach();
            $otherPost->images()->detach();
            $comments = $otherPost->allComments()->get();

            foreach ($comments as $comment) {
                $comment->comments_liked()->detach();
                $dependencies = Comment::where('parent_id', $comment->id)->update(['parent_id' => null]);
                $comment->update(['parent_id' => null]);
                $comment->delete();
            }
            $otherPost->notifications()->delete();
            $otherPost->delete();
        }
    }

    // --- Blockable ---

    public function blockedUsers()
    {
        return $this->morphToMany('App\Models\User', 'blockable', 'blockables');
    }

    public function blockedBy(): MorphToMany
    {
        return $this->morphedByMany('App\Models\User', 'blockable', 'blockables');
    }

    /**
     * Checks if user is blocked by another user
     */
    public function isBlockedBy(User $user): bool
    {
        return $this->blockedBy()->where('user_id', $user->id)->count() > 0;
    }

    public function block(Blockable $resource)
    {
        $resource->blockedBy()->save($this);
    }

    // --- %%Can Own ---

    /**
     * Check if user owns an ownable resource
     */
    public function isOwner(Ownable $resource): bool
    {
        return $resource->getOwner()->contains(function ($value, $key) {
            return $value->id === $this->id;
        });
    }
}
