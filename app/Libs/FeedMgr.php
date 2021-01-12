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

// [ ] %TODO: Pinned posts

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
    public static function getPosts(User $follower, array $filters=[]) : ?LengthAwarePaginator
    {
        //$followingIds = filterByBlockedFollowings();
        //$timeline = $follower->timeline;

        $query = Post::where('active', 1);

        if ( array_key_exists('hashtag', $filters) && !empty($filters['hashtag']) ) {
            $hashtag = $filters['hashtag'];
            $query->where('descripton', 'LIKE', "%{$hashtag}%");
        }

        if ( array_key_exists('start_date', $filters) && !empty($filters['start_date']) ) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        // belongs to timeline(s) that I'm following
        $query->where( function($q1) use(&$follower) {
            $q1->whereIn('timeline_id', $follower->followedtimelines->pluck('id'));
        });

        // or posts I follow directly %TODO

        // or posts I've purchased %TODO

        // %TODO: TEST: ensure no duplicates
        $posts = $query->latest()->paginate(Setting::get('items_page'));
        return $posts;
    }

    // %NOTE $follower could be null for guest user  %TODO
    public static function getPostsByTimeline(User $follower, Timeline $timeline, array $filters=[], $sortBy='asc', $take=10) : ?LengthAwarePaginator
    {
        //$followingIds = filterByBlockedFollowings();
        //$timeline = $follower->timeline;

        $query = Post::where('active', 1);

        if ( array_key_exists('hashtag', $filters) && !empty($filters['hashtag']) ) {
            $hashtag = $filters['hashtag'];
            $query->where('descripton', 'LIKE', "%{$hashtag}%");
        }

        if ( array_key_exists('start_date', $filters) && !empty($filters['start_date']) ) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        // belongs to timeline(s) that I'm following
        $query->where( function($q1) use(&$follower, &$timeline) {
            $q1->where('timeline_id', $timeline->id);
        });

        // or posts I follow directly %TODO

        // or posts I've purchased %TODO

        // %TODO: TEST: ensure no duplicates
        return $query->latest()->paginate($take);
    }

}
