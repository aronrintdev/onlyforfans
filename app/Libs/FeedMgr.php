<?php
namespace App\Libs;

use Illuminate\Support\Str;
use Illuminate\Support\Collection; 
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

use DB;
use Exception;
use Throwable;

use App\Models\Setting;
use App\Models\Post;
use App\Models\User;
use App\Models\Timeline;
use App\Enums\PaymentTypeEnum;

// [ ] %TODO: Pinned posts

class FeedMgr {

    public static function getSuggestedUsers(User $follower, array $attrs=[]) : ?Collection
    {
        // get random timelines I'm not currently following
        $max = Setting::get('min_items_page', 3);
        $suggested = User::inRandomOrder()
            ->where('id', '<>', $follower->id)
            ->whereNotIn('postable_id', $follower->followedtimelines->pluck('id') )
            ->take($max)
            ->get();
        return $suggested;
            
    }

    public static function getPosts(User $follower, array $filters=[], $page=1, $take=5) : ?LengthAwarePaginator
    {
        // %NOTE %TODO: Filter/distinguish free vs non-free posts in followed timelines

        //$followingIds = filterByBlockedFollowings();

        //$query = Post::with('mediafiles', 'user', 'timeline', 'comments.user')->where('active', 1);
        $query = Post::with('mediafiles', 'user', 'comments')->where('active', 1);

        $followedTimelineIDs = $follower->followedtimelines->pluck('id');
        $followedTimelineIDs->push($follower->timeline->id); // include follower's own timeline %NOTE
        // %NOTE %TODO: ^^^ this will not pick up user's own posts that are not free (??)


        // --- Belongs to timeline(s) that I'm following ---
        $query->where( function($q1) use(&$follower, $followedTimelineIDs) {
            $q1->whereIn('postable_id', $followedTimelineIDs);
        });


        // --- Apply optional filters ---

        if ( array_key_exists('hashtag', $filters) && !empty($filters['hashtag']) ) {
            $hashtag = $filters['hashtag'];
            $query->where('description', 'LIKE', "%{$hashtag}%");
        }

        if ( array_key_exists('start_date', $filters) && !empty($filters['start_date']) ) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        // or my own posts

        // or posts I follow directly %TODO

        // or posts I've purchased %TODO

        // %TODO: TEST: ensure no duplicates
        $posts = $query->latest()->paginate($take);
        return $posts;
    }

    public static function getPostsRaw(User $follower, array $filters=[]) : ?Collection
    {
        $query = Post::with('mediafiles')->where('active', 1);

        if ( array_key_exists('hashtag', $filters) && !empty($filters['hashtag']) ) {
            $hashtag = $filters['hashtag'];
            $query->where('description', 'LIKE', "%{$hashtag}%");
        }

        if ( array_key_exists('start_date', $filters) && !empty($filters['start_date']) ) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        // belongs to timeline(s) that I'm following
        $query->where( function($q1) use(&$follower) {
            $q1->whereIn('postable_id', $follower->followedtimelines->pluck('id'));
        });

        // or posts I follow directly %TODO

        // or posts I've purchased %TODO

        // %TODO: TEST: ensure no duplicates
        $posts = $query->latest()->get();
        return $posts;
    }


    // %NOTE $follower could be null for guest user  %TODO
    public static function getPostsByTimeline(User $follower, Timeline $timeline, array $filters=[], $sortBy='asc', $take=5) : ?LengthAwarePaginator
    {
        //$followingIds = filterByBlockedFollowings();
        //$timeline = $follower->timeline;

        $query = Post::where('active', 1);

        if ( array_key_exists('hashtag', $filters) && !empty($filters['hashtag']) ) {
            $hashtag = $filters['hashtag'];
            $query->where('description', 'LIKE', "%{$hashtag}%");
        }

        if ( array_key_exists('start_date', $filters) && !empty($filters['start_date']) ) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        // belongs to timeline(s) that I'm following
        $query->where( function($q1) use(&$follower, &$timeline) {
            $q1->where('postable_id', $timeline->id);
        });

        // or posts I follow directly %TODO

        // or posts I've purchased %TODO

        // %TODO: TEST: ensure no duplicates
        return $query->latest()->paginate($take);
    }

}
