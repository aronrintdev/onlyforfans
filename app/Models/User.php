<?php
namespace App\Models;

use App\Enums\Financial\AccountTypeEnum;
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
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements PaymentSendable, Blockable, HasFinancialAccounts
{
    use Notifiable, HasRoles, HasFactory, Messagable, SoftDeletes, UsesUuid, MorphFunctions;

    protected $appends = [ 'name', 'avatar', 'about', ];
    protected $guarded = [ 'id', 'created_at', 'updated_at' ];
    protected $hidden = [ 'email', 'password', 'remember_token', 'verification_code', 'timeline'];
    protected $dates = [ 'last_logged', ];

    //--------------------------------------------
    // Boot
    //--------------------------------------------
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


    // Makes username a valid random username if it is null or empty.
    public function checkUsername()
    {
        if (!isset($this->username) || $this->username === '') {
            $this->username = UsernameRule::createRandom();
        }
    }

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function settings() {
        return $this->hasOne(UserSetting::class);
    }

    public function sharedmediafiles()
    { // Mediafiles shared with me (??)
        return $this->morphedByMany('App\Models\Mediafile', 'shareable', 'shareables', 'sharee_id')
            ->withTimestamps();
    }

    public function sharedvaultfolders()
    { // Vaultfolders shared with me (??)
        return $this->morphedByMany('App\Models\Vaultfolder', 'shareable', 'shareables', 'sharee_id')
            ->withTimestamps();
    }

    public function bookmarks()
    { 
        return $this->hasMany('App\Models\Bookmark', 'user_id');
    }

    public function ledgersales()
    {
        return $this->hasMany('App\Models\Fanledger', 'seller_id');
    }

    public function ledgerpurchases()
    {
        return $this->hasMany('App\Models\Fanledger', 'purchaser_id');
    }

    public function timeline()
    {
        return $this->hasOne('App\Models\Timeline');
    }

    public function posts()
    {
        return $this->hasMany('App\Models\Post', 'user_id');
    }

    // timelines (users) I follow: premium *and* default subscribe (follow)
    public function followedtimelines()
    {
        return $this->morphedByMany('App\Models\Timeline', 'shareable', 'shareables', 'sharee_id')
            ->withPivot('access_level', 'shareable_type', 'sharee_id')->withTimestamps();
    }

    public function subscribedtimelines()
    {
        return $this->morphedByMany('App\Models\Timeline', 'shareable', 'shareables', 'sharee_id')
            ->where('access_level', 'premium')
            ->withPivot('access_level', 'shareable_type', 'sharee_id')->withTimestamps();
    }

    public function likedposts()
    {
        return $this->morphedByMany('App\Models\Post', 'likeable', 'likeables', 'likee_id')
            ->withTimestamps();
    }

    // posts shared with me (by direct share or purchase on my part)
    public function sharedposts()
    {
        return $this->morphedByMany('App\Models\Post', 'shareable', 'shareables', 'sharee_id')->withTimestamps();
    }

    public function postsPinned()
    {
        return $this->belongsToMany('App\Post', 'pinned_posts', 'user_id', 'post_id');
    }

    public function userList()
    {
        return $this->belongsToMany('App\UserListType', 'user_lists', 'user_id', 'list_type_id');
    }

    public function own_pages()
    {
        $admin_role_id = Role::where('name', 'admin')->first();
        $own_pages = $this->pages()->where('role_id', $admin_role_id->id)->where('page_user.active', 1)->get();
        $result = $own_pages ? $own_pages : false;
        return $result;
    }

    public function own_groups()
    {
        $admin_role_id = Role::where('name', '=', 'admin')->first();
        $own_groups = $this->groups()->where('role_id', $admin_role_id->id)->where('status', 'approved')->get();
        $result = $own_groups ? $own_groups : false;
        return $result;
    }

    public function payment()
    {
        return $this->hasOne('App\Payment');
    }

    public function groups()
    {
        return $this->belongsToMany('App\Group', 'group_user', 'user_id', 'group_id')->withPivot('role_id', 'status');
    }

    public function pageLikes()
    {
        return $this->belongsToMany('App\Page', 'page_likes', 'user_id', 'page_id');
    }
    
    public function notifications()
    {
        return $this->hasMany('App\Models\Notification')->with('notified_from');
    }

    public function vaults()
    {
        return $this->hasMany('App\Models\Vault');
    }

    public function vaultfolders()
    {
        return $this->hasMany('App\Models\Vaultfolder');
    }

    public function financialAccounts()
    {
        return $this->morphMany(Account::class, 'owner');
    }


    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    // https://stackoverflow.com/questions/30226496/how-to-cast-eloquent-pivot-parameters
    /* %PSG: could not get this to work, just do 'manually' in controller or other calling code
    protected $casts = [
        'sharedposts.pivot.cattrs' => 'array',
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

    public function getAboutAttribute($value)
    {
        return $this->timeline->about ? $this->timeline->about : null;
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

    public function commentLikes()
    {
        return $this->morphedByMany('App\Models\Comment', 'likeable', 'likeables', 'likee_id')
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

    // Checks if user is blocked by another user
    public function isBlockedBy(User $user): bool
    {
        return $this->blockedBy()->where('user_id', $user->id)->count() > 0;
    }

    public function block(Blockable $resource)
    {
        $resource->blockedBy()->save($this);
    }

    // --- %%Can Own ---

    // Check if user owns an ownable resource
    public function isOwner(Ownable $resource): bool
    {
        return $resource->getOwner()->contains(function ($value, $key) {
            return $value->id === $this->id;
        });
    }

    public function events()
    {
        return $this->belongsToMany('App\Event', 'event_user', 'user_id', 'event_id');
    }

    public function get_eventuser($id)
    {
        return $this->events()->where('events.id', $id)->first();
    }

    public function isAdmin() : bool
    {
        return $this->roles()->pluck('name')->contains('super-admin');
    }

    public function is_eventadmin($user_id, $event_id) {
        $chk_isadmin = Event::where('id', $event_id)->where('user_id', $user_id)->first();

        $result = $chk_isadmin ? true : false;

        return $result;
    }

    public function getEvents()
    {
        $result = [];
        $guestevents =  $this->events()->get();
        if ($guestevents) {
            foreach ($guestevents as $guestevent) {
                if (!$this->is_eventadmin(Auth::user()->id, $guestevent->id)) {
                    array_push($result, $guestevent);
                }
            }
        }
        return $result;
    }

    public function is_groupAdmin($user_id, $group_id)
    {
        $admin_role_id = Role::where('name', 'admin')->first();
        $groupUser = $this->groups()->where('group_id', $group_id)->where('user_id', $user_id)->where('role_id', $admin_role_id->id)->where('status', 'approved')->first();
        return $groupUser ? true : false;
    }

    public function is_groupMember($user_id, $group_id)
    {
        $admin_role_id = Role::where('name', 'admin')->first();
        $groupMember = $this->groups()->where('group_id', $group_id)->where('user_id', $user_id)->where('role_id', '!=', $admin_role_id->id)->where('status', 'approved')->first();
        return $groupMember ? true : false;
    }

    // total sales in cents
    public function getSales() : int
    {
        return Fanledger::where('seller_id', $this->id)->sum('total_amount');
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
            'earnings'         => $this->getSales(),
            'website'          => '', // %TODO
            'instagram'        => '', // %TODO
            'city'             => $this->settings->city,
            'country'          => $this->settings->country,
        ];
    }

    public function hasEarnings() : bool
    {
        $sales = $this->getSales();
        return $sales > 0;
    }


    /* ------------------------ HasFinancialAccounts ------------------------ */
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

}

    /*
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
     */

