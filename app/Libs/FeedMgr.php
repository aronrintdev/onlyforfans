<?php
namespace App\Libs;

use Illuminate\Support\Str;
use Illuminate\Support\Collection; 
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

use DB;
use Exception;
use Throwable;

use App\Setting;
use App\Post;
use App\User;
use App\Timeline;
use App\Enums\PaymentTypeEnum;

class FeedMgr {

    public static function getSuggestedUsers(User $follower, array $attrs=[]) : ?Collection
    {
        // get random timlines I'm not currently following
        $max = Setting::get('min_items_page', 3);
        $suggested = User::inRandomOrder()
            ->where('id', '<>', $follower->id)
            ->whereNotIn( 'timeline_id', $follower->followedtimelines->pluck('id') )
            ->take($max)
            ->get();
        return $suggested;
            
    }

    //public static function getPosts(User $follower, array $attrs=[]) : ?Collection
    public static function getPosts(User $follower, array $attrs=[]) : ?LengthAwarePaginator
    {
        //$followingIds = filterByBlockedFollowings();
        //$timeline = $follower->timeline;

        $query = Post::where('active', 1);

        if ( array_key_exists('hashtag', $attrs) && !empty($attrs['hashtag']) ) {
            $hashtag = $attrs['hashtag'];
            $query->where('descripton', 'LIKE', "%{$hashtag}%");
        }

        // belongs to timeline(s) that I'm following
        $query->where( function($q1) use(&$follower) {
            $q1->whereIn('timeline_id', $follower->followedtimelines->pluck('id'));
        });

        // or posts I follow directly 

        // or posts I've purchased

        // TEST: ensure no duplicates
        //$posts = $query->latest()->get();
        $posts = $query->latest()->paginate(Setting::get('items_page'));

            /*

        if ( array_key_exists('hashtag', $attrs) && !empty($attrs['hashtag']) ) {
            $hashtag = $attrs['hashtag'];
            $followerIDs = DB::table('followers')->where('follower_id', $follower->id)->whereIn('leader_id', $followingIds)->pluck('leader_id');
            $posts = Post::where('description', 'like', "%{$hashtag}%")
                ->where('active', 1)
                ->whereIn('timeline_id', $followerIDs)
                ->latest()
                ->paginate(Setting::get('items_page'));
        } else {
            // show the normal feed
            $query = Post::where('active', 1)->whereIn('user_id', function ($query) use (&$follower, $followingIds, $timeline) {
                $query->select('leader_id')->from('followers')->where('follower_id', $follower->id)->whereIn('leader_id', $followingIds);
            })->orWhere(function ($query) use (&$follower, $timeline) {
                $query->whereIn('id', function ($query1) use (&$follower, $timeline) {
                    $query1->select('post_id')->from('pinned_posts')->where('user_id', $follower->id);
                })->orWhere('user_id', $follower->id)
                  ->orWhere('timeline_id', $timeline->id);
            });
            $posts = $query->where('active', 1)->latest()->paginate(Setting::get('items_page'));
        }
             */

        return $posts;
    }

}
