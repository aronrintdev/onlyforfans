<?php
namespace App\Models;

use App\Enums\Financial\AccountTypeEnum;
use App\Enums\ShareableAccessLevelEnum;
use DB;
use Auth;

use App\Interfaces\Ownable;
use App\Interfaces\Blockable;
use App\Interfaces\HasFinancialAccounts;
use App\Interfaces\ShortUuid;
use App\Models\Traits\UsesUuid;

use App\Interfaces\PaymentSendable;
use App\Models\Financial\Account;
use App\Models\Traits\MorphFunctions;
use App\Models\Traits\UsesShortUuid;
use Carbon\Carbon;
use Spatie\Permission\Traits\HasRoles;
use Cmgmyr\Messenger\Traits\Messagable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use InvalidArgumentException;
use Laravel\Scout\Searchable;

/**
 * @property string      $id              `uuid` | `unique`
 * @property string      $email           email address | `unique`
 * @property string      $username        `unique`
 * @property string      $firstname       User defined First Name
 * @property string      $lastname        User defined Last Name
 * @property string      $password        Password Hash
 * @property string      $remember_token  Laravel remember token
 * @property bool        $email_verified  If email has been verified
 * @property bool        $is_online       If user is currently online
 * @property Carbon|null $last_logged     Last login time of user
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon|null $deleted_at
 *
 * @package App\Models
 */
class User extends Authenticatable implements Blockable, HasFinancialAccounts
{
    use HasRoles,
        HasFactory,
        Messagable,
        SoftDeletes,
        UsesUuid,
        MorphFunctions,
        Notifiable,
        Searchable;

    /* ---------------------------------------------------------------------- */
    /*                            Model Properties                            */
    /* ---------------------------------------------------------------------- */
    #region Model Properties
    protected $connection = 'primary';

    protected $appends = [
        'name',
        'avatar',
        'cover',
        'about',
    ];

    protected $guarded = [
        'id',
        // 'password',
        'remember_token',
        'verification_code',
        // 'email_verified',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'email',
        'password',
        'remember_token',
        'verification_code',
        'timeline'
    ];

    protected $dates = [
        'last_logged',
    ];

    #endregion Model Properties
    /* ---------------------------------------------------------------------- */

    /* ---------------------------------------------------------------------- */
    /*                                  Boot                                  */
    /* ---------------------------------------------------------------------- */
    #region Boot
    /**
     * Laravel Boot function
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->checkUsername();
            $model->remember_token = str_random(10);
            $model->verification_code = str_random(10);
        });
        self::created(function ($model) {
            UserSetting::create([
                'user_id' => $model->id,
            ]);
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

    #endregion Boot
    /* ---------------------------------------------------------------------- */


    // Makes username a valid random username if it is null or empty.
    public function checkUsername()
    {
        if (!isset($this->username) || $this->username === '') {
            $this->username = UsernameRule::createRandom();
        }
    }


    /* ---------------------------------------------------------------------- */
    /*                              Relationships                             */
    /* ---------------------------------------------------------------------- */
    #region Relationships

    public function settings() {
        return $this->hasOne(UserSetting::class);
    }

    public function mediafiles()
    {
        return $this->morphMany(Mediafile::class, 'resource');
    }

    public function sharedmediafiles()
    { // Mediafiles shared with me (??)
        return $this->morphedByMany(Mediafile::class, 'shareable', 'shareables', 'sharee_id')
            ->withTimestamps();
    }

    public function sharedvaultfolders()
    { // Vaultfolders shared with me (??)
        return $this->morphedByMany(Vaultfolder::class, 'shareable', 'shareables', 'sharee_id')
            ->withTimestamps();
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }

    public function timeline()
    {
        return $this->hasOne(Timeline::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function mycontacts()
    {
        return $this->belongsToMany(User::class, 'mycontacts', 'owner_id', 'contact_id');
    }

    // timelines (users) I follow: premium *and* default subscribe (follow)
    public function followedtimelines()
    {
        return $this->morphedByMany(Timeline::class, 'shareable', 'shareables', 'sharee_id')
            ->withPivot('access_level', 'shareable_type', 'sharee_id')->withTimestamps();
    }

    public function followedForFreeTimelines()
    {
        return $this->morphedByMany(Timeline::class, 'shareable', 'shareables', 'sharee_id')
            ->where('access_level', ShareableAccessLevelEnum::DEFAULT)
            ->withPivot('access_level', 'shareable_type', 'sharee_id')->withTimestamps();
    }

    /**
     * Posts user has purchased premium access to
     */
    public function purchasedPosts()
    {
        return $this->morphedByMany(Post::class, 'shareable', 'shareables', 'sharee_id')
            ->where('access_level', ShareableAccessLevelEnum::PREMIUM)
            ->withPivot('access_level', 'shareable_type', 'sharee_id')->withTimestamps();
    }

    public function subscribedtimelines()
    {
        return $this->morphedByMany(Timeline::class, 'shareable', 'shareables', 'sharee_id')
            ->where('access_level', ShareableAccessLevelEnum::PREMIUM)
            ->withPivot('access_level', 'shareable_type', 'sharee_id')->withTimestamps();
    }

    public function likedposts()
    {
        return $this->morphedByMany(Post::class, 'likeable', 'likeables', 'liker_id')
            ->withTimestamps();
    }

    // posts shared with me (by direct share or purchase on my part)
    public function sharedposts()
    {
        return $this->morphedByMany(Post::class, 'shareable', 'shareables', 'sharee_id')->withTimestamps();
    }

    public function postsPinned()
    {
        return $this->belongsToMany(Post::class, 'pinned_posts', 'user_id', 'post_id');
    }

    public function userList()
    {
        return $this->belongsToMany('App\UserListType', 'user_lists', 'user_id', 'list_type_id');
    }

    public function vaults()
    {
        return $this->hasMany(Vault::class);
    }

    public function vaultfolders()
    {
        return $this->hasMany(Vaultfolder::class);
    }

    public function financialAccounts()
    {
        return $this->morphMany(Account::class, 'owner');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function chatthreads() // ie threads this user 'participates' in
    {
        return $this->belongsToMany(Chatthread::class, 'chatthread_user', 'user_id', 'chatthread_id');
    }

    public function sentmessages()
    {
        return $this->hasMany(Chatmessage::class, 'sender_id');
    }

    public function commentLikes()
    {
        return $this->morphedByMany(Comment::class, 'likeable', 'likeables', 'liker_id')
            ->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

//    public function lists()
//    {
//        return $this->belongsToMany(Lists::class, 'list_user', 'user_id', 'list_id')->withTimestamps();
//    }

//    public function userlists()
//    {
//        return $this->hasMany(Lists::class, 'creator_id');
//    }

    #endregion Relationships
    /* ---------------------------------------------------------------------- */

    /* ---------------------------------------------------------------------- */
    /*                       Accessors/Mutators | Casts                       */
    /* ---------------------------------------------------------------------- */
    #region Accessors/Mutators | Casts

    // https://stackoverflow.com/questions/30226496/how-to-cast-eloquent-pivot-parameters
    /* %PSG: could not get this to work, just do 'manually' in controller or other calling code
    protected $casts = [
        'sharedposts.pivot.cattrs' => 'array',
        'sharedposts.pivot.meta' => 'array',
    ];
     */

    public function getNameAttribute($value)
    {
        return $this->firstname;
    }

    public function getAvatarAttribute($value)
    {
        return ($this->timeline && $this->timeline->avatar)
            ? $this->timeline->avatar
            : (object) ['filepath' => url('/images/default_avatar.svg')];
            // : (object) ['filepath' => url('user/avatar/default-' . $this->gender . '-avatar.png')];
    }

    public function getCoverAttribute($value)
    {
        return ($this->timeline && $this->timeline->cover)
            ? $this->timeline->cover
            : (object) ['filepath' => url('/images/locked_post.png')];
            //: (object) ['filepath' => url('user/cover/default-' . $this->gender . '-cover.png')]; // %TODO %FIXME
    }

    public function getAboutAttribute($value)
    {
        return ($this->timeline && $this->timeline->about) ? $this->timeline->about : null;
    }

    #endregion Accessors/Mutators | Casts
    /* ---------------------------------------------------------------------- */

    /* ---------------------------------------------------------------------- */
    /*                               Searchable                               */
    /* ---------------------------------------------------------------------- */
    #region Searchable

    /**
     * Name of the search index associated with this model
     * @return string
     */
    public function searchableAs()
    {
        return "users_index";
    }

    /**
     * Get value used to index the model
     * @return mixed
     */
    public function getScoutKey()
    {
        return $this->getKey();
    }

    /**
     * Get key name used to index the model
     * @return string
     */
    public function getScoutKeyName()
    {
        return 'id';
    }

    /**
     * What model information gets stored in the search index
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'name'     => isset($this->timeline) ? $this->timeline->name : '',
            'slug'     => isset($this->timeline) ? $this->timeline->slug : '',
            'username' => $this->username,
            'email'    => $this->email,
            'id'       => $this->getKey(),
        ];
    }

    #endregion Searchable
    /* ---------------------------------------------------------------------- */

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

    public function deleteOthers()
    {
        // %PSG: Delete posts that aren't my own from my timeline
        $sessionUser = Auth::user();
        $otherPosts = $this->timeline->posts()->where('user_id', '!=', $sessionUser->id)->get();
        foreach ($otherPosts as $otherPost) {
            $otherPost->users_liked()->detach();
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
            $otherPost->delete();
        }
    }

    /* ---------------------------------------------------------------------- */
    /*                                Blockable                               */
    /* ---------------------------------------------------------------------- */
    #region Blockable

    public function blockedUsers()
    {
        return $this->morphToMany(User::class, 'blockable', 'blockables');
    }

    public function blockedBy(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'blockable', 'blockables');
    }

    // Checks if user is blocked by another user
    public function isBlockedBy(User $user): bool
    {
        return $this->blockedBy()->where('user_id', $user->id)->count() > 0;
    }

    public function block(Blockable $resource)
    {
        $resource->blockedBy()->save($this);
    }

    #endregion Blockable
    /* ---------------------------------------------------------------------- */

    /* ---------------------------------------------------------------------- */
    /*                                 Can Own                                */
    /* ---------------------------------------------------------------------- */
    #region Can Own

    // Check if user owns an ownable resource
    public function isOwner(Ownable $resource): bool
    {
        return $resource->getOwner()->contains(function ($value, $key) {
            return $value->id === $this->id;
        });
    }

    #endregion Can Own
    /* ---------------------------------------------------------------------- */


    public function isAdmin() : bool
    {
        return $this->roles()->pluck('name')->contains('super-admin');
    }


    // total sales in cents
    public function getSales() : int
    {
        // TODO: Hook up to earnings controller
        return 0;
    }


    public function getStats() : array
    {
        $timeline = $this->timeline;
        if ( !$timeline ) {
            return [];
        }
        return [
            'post_count'       => $timeline->posts->count(),
            'like_count'       => $timeline->user->likedposts->count(),
            'follower_count'   => $timeline->followers->count(),
            'following_count'  => $timeline->user->followedtimelines->count(),
            'subscribed_count' => 0, // %TODO $sessionUser->timeline->subscribed->count()
            'earnings'         => '', // TODO: Hook up to earnings controller
            'website'          => '', // %TODO
            'instagram'        => '', // %TODO
            'city'             => (isset($this->settings)) ? $this->settings->city : null,
            'country'          => (isset($this->settings)) ? $this->settings->country : null,
        ];
    }

    public function hasEarnings() : bool
    {
        $sales = $this->getSales();
        return $sales > 0;
    }

    /* ---------------------------------------------------------------------- */
    /*                          HasFinancialAccounts                          */
    /* ---------------------------------------------------------------------- */
    #region HasFinancialAccounts
    public function getInternalAccount(string $system, string $currency): Account
    {
        $account = $this->financialAccounts()->where('system', $system)
            ->where('currency', $currency)
            ->where('type', AccountTypeEnum::INTERNAL)
            ->first();
        if (isset($account)) {
            return $account;
        }
        return $this->createInternalAccount($system, $currency);
    }

    public function createInternalAccount(string $system, string $currency): Account
    {
        $account = Account::create([
            'system' => $system,
            'owner_type' => $this->getMorphString(),
            'owner_id' => $this->getKey(),
            'name' => $this->username . ' Balance Account',
            'type' => AccountTypeEnum::INTERNAL,
            'currency' => $currency,
            'balance' => 0,
            'balance_last_updated_at' => Carbon::now(),
            'pending' => 0,
            'pending_last_updated_at' => Carbon::now(),
        ]);
        $account->verified = true;
        $account->can_make_transactions = true;
        $account->save();
        return $account;
    }

    #endregion HasFinancialAccounts
    /* ---------------------------------------------------------------------- */

    // %%% --- Misc. ---


}

    /*
//    public function getOthersSettings($username)
//    {
//        $timeline = Timeline::where('username', $username)->first();
//        $user = self::where('timeline_id', $timeline->id)->first();
//        $result = DB::table('user_settings')->where('user_id', $user->id)->first();
//
//        return $result;
//    }
//    public function getUserPrivacySettings($loginId, $others_id)
//    {
//        $timeline_post_privacy = '';
//        $timeline_post = '';
//        $user_post = '';
//        $result = '';
//
//        $live_user_settings = $this->getUserSettings($others_id);
//
//        if ($live_user_settings) {
//            $timeline_post_privacy = $live_user_settings->timeline_post_privacy;
//            $user_post_privacy = $live_user_settings->post_privacy;
//        }
//
//        //start $this if block is for timeline post privacy settings
//        if ($loginId != $others_id) {
//            if ($timeline_post_privacy != null && $timeline_post_privacy == 'only_follow') {
//                $isFollower = $this->chkMyFollower($others_id, $loginId);
//                if ($isFollower) {
//                    $timeline_post = true;
//                }
//            } elseif ($timeline_post_privacy != null && $timeline_post_privacy == 'everyone') {
//                $timeline_post = true;
//            } elseif ($timeline_post_privacy != null && $timeline_post_privacy == 'nobody') {
//                $timeline_post = false;
//            }
//
//            //start $this if block is for user post privacy settings
//            if ($user_post_privacy != null && $user_post_privacy == 'only_follow') {
//                $isFollower = $this->chkMyFollower($others_id, $loginId);
//                if ($isFollower) {
//                    $user_post = 'user';
//                }
//            } elseif ($user_post_privacy != null && $user_post_privacy == 'everyone') {
//                $user_post = 'user';
//            }
//        } else {
//            $timeline_post = true;
//            $user_post = 'user';
//        }
//        //End
//        $result = $timeline_post . '-' . $user_post;
//
//        return $result;
//    }
     */

