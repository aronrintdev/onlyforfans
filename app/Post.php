<?php

namespace App;

use DB;
use Auth;
use App\User;
use Exception;
use Carbon\Carbon;
use App\Enums\PostTypeEnum;
use App\Interfaces\Ownable;
use App\Interfaces\Deletable;
use App\Enums\PaymentTypeEnum;
use App\Interfaces\PaymentReceivable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model implements Ownable, Deletable, PaymentReceivable
{

    use SoftDeletes;
    use HasFactory;

    //--------------------------------------------
    // Boot
    //--------------------------------------------
    public static function boot()
    {
        parent::boot();
        static::deleting(function ($model) {
            if ( !$model->canBeDeleted() ) {
                throw new Exception('Can not delete Post (26)'); // or soft delete and give access to purchasers (?)
            }
            foreach ($model->mediafiles as $o) {
                Storage::disk('s3')->delete($o->filename); // Remove from S3
                $o->delete();
            }
            foreach ($model->comments as $o) {
                $o->delete();
            }
            foreach ($model->likes as $o) {
                $o->delete();
            }
        });
    }

    // %TODO: remove these and use enum instead
    const FREE_TYPE = PostTypeEnum::FREE; // 'free';
    const PRICE_TYPE = PostTypeEnum::PRICED; // 'price'; // associated with a price
    const PAID_TYPE = PostTypeEnum::SUBSCRIBER; // 'paid'; // %PSG: ie, for subscribers (?)
    
    //use SoftDeletes;
    //protected $dates = ['deleted_at'];

    protected $guarded = ['id','created_at','updated_at'];

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function sharees() { // can be shared with many users (via [shareables])
        return $this->morphToMany('App\User', 'shareable', 'shareables', 'shareable_id', 'sharee_id')->withTimestamps();
    }
    public function likes() {
        return $this->morphToMany('App\User', 'likeable', 'likeables', 'likeable_id', 'likee_id')->withTimestamps();
    }
    //public function shares() {
        //return $this->belongsToMany('App\User', 'post_shares', 'post_id', 'user_id');
    //}
    //public function users_shared() {
        //return $this->belongsToMany('App\User', 'post_shares', 'post_id', 'user_id');
    //}

    public function mediafiles() {
        return $this->morphMany('App\Mediafile', 'resource');
    }

    public function ledgersales() {
        return $this->morphMany('App\Fanledger', 'purchaseable');
    }

    public function user() { // owner of the post
        return $this->belongsTo('App\User');
    }

    public function getOwner() : ?User {
        return $this->user;
    }

    public function timeline() {
        return $this->belongsTo('App\Timeline');
    }


    //public function users_liked() { // %TODO %DEPRECATE
        //return $this->belongsToMany('App\User', 'post_likes', 'post_id', 'user_id')->withTimestamps();
    //}

    //public function tip() {
        //return $this->belongsToMany('App\User', 'post_tips', 'post_id', 'user_id')->withPivot('amount')->withTimestamps();
    //}


    public function usersSaved() {
        return $this->belongsToMany('App\User', 'saved_posts', 'post_id', 'user_id');
    }

    public function usersPinned() {
        return $this->belongsToMany('App\User', 'pinned_posts', 'post_id', 'user_id');
    }

    //public function notifications_user() {
        //return $this->belongsToMany('App\User', 'post_follows', 'post_id', 'user_id')->withTimestamps();
    //}

    public function reports() {
        return $this->belongsToMany('App\User', 'post_reports', 'post_id', 'reporter_id')->withPivot('status');
    }

    public function comments() {
        return $this->hasMany('App\Comment')->where('parent_id', null);
    }

    public function images() {
        return $this->belongsToMany('App\Media', 'post_media', 'post_id', 'media_id');
    }

    public function videos() {
    }

    public function users_posts() {
        return $this->belongsToMany('App\User', 'posts', 'id', 'user_id');
    }

    public function managePostReport($post_id, $user_id) {
        $post_report = DB::table('post_reports')->insert(['post_id' => $post_id, 'reporter_id' => $user_id, 'status' => 'pending', 'created_at' => Carbon::now()]);

        $result = $post_report ? true : false;

        return $result;
    }

    public function check_reports($post_id)
    {
        $post_report = DB::table('post_reports')->where('post_id', $post_id)->first();

        $result = $post_report ? true : false;

        return $result;
    }

    public function deleteManageReport($id)
    {
        $post_report = DB::table('post_reports')->where('id', $id)->delete();

        $result = $post_report ? true : false;

        return $result;
    }

    public function getUserName($id)
    {
        $user = User::find($id);
        $timeline = Timeline::where('id', $user->timeline_id)->first();
        $result = $timeline ? $timeline->username : false;

        return $result;
    }

    public function getAvatar($id)
    {
        $user = User::find($id);
        $timeline = Timeline::where('id', $user->timeline_id)->first();
        $media = Media::where('id', $timeline->avatar_id)->first();

        $result = $media ? $media->source : false;

        return $result;
    }

    public function getGender($id)
    {
        $user = User::find($id);

        $result = $user ? $user->gender : false;

        return $result;
    }

    public function postsLiked()
    {
        $result = DB::table('post_likes')->get();

        return $result;
    }

    public function postsReported()
    {
        $result = DB::table('post_reports')->get();

        return $result;
    }

    public function postShared()
    {
        $result = DB::table('post_shares')->get();

        return $result;
    }

    // Is the owner of this post an approved follower of the session user? | returns settings.comment_privacy field or boolean (?)
    public function chkUserFollower($login_id, $post_user_id)
    {
        //$isApprovedFollower = DB::table('followers')->where('follower_id', $post_user_id)->where('leader_id', $login_id)->where('status', 'approved')->count();

        $sessionUser = User::findOrFail($login_id);
        $postOwner = User::findOrFail($post_user_id);
        $isApprovedFollower = $sessionUser->timeline->followers->contains($postOwner->id);
        if ($isApprovedFollower) {
            $userSettings = DB::table('user_settings')->where('user_id', $login_id)->first();
            $result = $userSettings ? $userSettings->comment_privacy : false;
            return $result;
        }
        return null;
    }

    public function chkUserSettings($login_id)
    {
        $userSettings = DB::table('user_settings')->where('user_id', $login_id)->first();
        $result = $userSettings ? $userSettings->comment_privacy : false;

        return $result;
    }

    public function users_tagged()
    {
        return $this->belongsToMany('App\User', 'post_tags', 'post_id', 'user_id');
    }

    public function getPageName($id)
    {
        $timeline = Timeline::where('id', $id)->first();
        $result = $timeline ? $timeline->username : false;

        return $result;
    }

    public function deletePageReport($id)
    {
        $timeline_report = DB::table('timeline_reports')->where('id', $id)->delete();
        $result = $timeline_report ? true : false;

        return $result;
    }

    public function notifications()
    {
        return $this->hasMany('App\Notification', 'post_id', 'id');
    }

    //public function sharedPost() {
        //return $this->belongsTo('App\Post', 'id', 'shared_post_id');
    //}

    public function allComments()
    {
        return $this->hasMany('App\Comment');
    }

    public function deleteMe()
    {
        $this->users_liked()->detach();
        $this->shares()->detach();
        //$this->notifications_user()->detach();
        $this->reports()->detach();
        $this->users_tagged()->detach();
        $this->images()->detach();
        $comments = $this->allComments()->get();
        
        foreach ($comments as $comment) {
            $comment->comments_liked()->detach();
            $dependencies = Comment::where('parent_id', $comment->id)->update(['parent_id' => null]);
            $comment->update(['parent_id' => null]);
            $comment->delete();
        }

        $this->notifications()->delete();

        $this->delete();
    }

    //--------------------------------------------
    // %%% Methods
    //--------------------------------------------

    // %%% --- Implement PaymentReceivable Interface ---

    public function receivePayment(
        string $ptype, // PaymentTypeEnum
        User $sender,
        //PaymentSendable $sender,
        //PaymentReceivable $receiver, -> $this
        int $amountInCents,
        array $cattrs = []
    ) : ?Fanledger
    {
        $result = DB::transaction( function() use($ptype, $amountInCents, $cattrs, &$sender) {

            switch ($ptype) {
                case PaymentTypeEnum::TIP:
                    $result = Fanledger::create([
                        'fltype' => $ptype,
                        'seller_id' => $this->user->id,
                        'purchaser_id' => $sender->id,
                        'purchaseable_type' => 'posts',
                        'purchaseable_id' => $this->id,
                        'qty' => 1,
                        'base_unit_cost_in_cents' => $amountInCents,
                        'cattrs' => $cattrs ?? [],
                    ]);
                    break;
                case PaymentTypeEnum::PURCHASE:
                    $result = Fanledger::create([
                        'fltype' => $ptype,
                        'seller_id' => $this->user->id,
                        'purchaser_id' => $sender->id,
                        'purchaseable_type' => 'posts',
                        'purchaseable_id' => $this->id,
                        'qty' => 1,
                        'base_unit_cost_in_cents' => $amountInCents,
                        'cattrs' => $cattrs ?? [],
                    ]);
                    $sender->sharedposts()->attach($this->id, [
                        'cattrs' => json_encode($cattrs ?? []),
                    ]);
                    break;
                default:
                    throw new Exception('Unrecognized payment type : '.$ptype);
            }

            return $result;
        });

        return $result ?? null;
    }

    // Can a user view this post (?)
    public function isViewableByUser(User $viewingUser) : bool
    {
        $postOwner = $this->user;
/*
if($user->followers->contains($sessionUser->id) || $user->id == $sessionUser->id || $user->price == 0)
if(!$user->followers->contains($sessionUser->id) && $user->id != $sessionUser->id && $user->price > 0 )
 */

        if ( $postOwner->id === $viewingUser->id ) {
            return true; // users can always see own posts
        }

        if ( $viewingUser->sharedposts->contains('id', $this->id) ) {
            return true; // valid share, all types
        }

        switch ($this->type) {
            case PostTypeEnum::FREE:
                return true;
            case PostTypeEnum::PRICED:
                if( $postOwner->price == 0) {
                    return true;
                }
                break;
            case PostTypeEnum::SUBSCRIBER:
                if( $postOwner->timeline->followers->contains('id',$viewingUser->id) ) {
                    return true; // viewer is a follower
                } 
                if( $postOwner->price == 0) {
                    return true;
                }
                break;
            default:
                throw new Exception('Unrecognized Post type: '.$this->type);
        }

        return false;
    }

    public function isCommentSectionShown($sessionUser) : bool
    {
        $user_follower = $this->chkUserFollower($sessionUser->id,$this->user_id);
        $user_setting = $this->chkUserSettings($this->user_id);
    
        if ( !is_null($user_follower) && ($user_follower==="only_follow" || $user_follower==="everyone") ) {
            return true;
        } 
        if ( $user_setting && $user_setting == "everyone" ) {
            return true;
        }
        return false;
    }

    public function canBeDeleted() : bool
    {
        return !( $this->ledgersales->count() > 0 );
    }

}
