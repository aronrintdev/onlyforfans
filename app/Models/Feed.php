<?php
namespace App\Models;

use DB;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Enums\PostTypeEnum;

class Feed 
{
    // my posts: timeline = me
    // {username} posts: timeline = {username}
    // {followed timelines} posts: timelines = {all users I follow}
    // 

    // %TODO: how to use these as super-admin (?) - just provide a timeline, permissions checked in controller

    // *as owner viewing home, get posts for all timelines I follow
    // always non-strict !
    public static function getHomeFeed(User $user) : Collection
    {
        $query = Post::query();

        //if ( !$timeline->subscribers->contains($viewer->id) ) { }

        $query->whereHas('timeline', function($q1) use(&$user) {
            $q1->whereHas('followers', function($q2) use(&$user) {
                $q2->where('id', $user->id);
            });
        });

        $posts = $query->get();
        return $posts;
    }

    // get all by timeline (eg: as owner viewing own timeline, get all by timeline)
    public static function getOwnerFeed(Timeline $timeline) : Collection
    {
        $query = Post::query();
        $query->where('postable_type', 'timelines')
              ->where('postable_id', $timeline->id);
        $posts = $query->get();
        return $posts;
    }

    // get free by timeline (eg: as fan viewing non-followed timeline)
    public static function getPublicFeed(Timeline $timeline) : Collection
    {
        $query = Post::query();
        $query->where('postable_type', 'timelines')
              ->where('postable_id', $timeline->id)
              ->where('type', PostTypeEnum::FREE);
        $posts = $query->get();
        return $posts;
    }

    // get free + post-purchased-by-user by timeline (eg: as fan viewing followed timeline)
    public static function getFollowerFeed(Timeline $timeline, User $viewer) : Collection
    {
        $query = Post::query();

        $query->where('postable_type', 'timelines')
              ->where('postable_id', $timeline->id)
              ->where( function($q1) use(&$viewer) {
                  $q1->where('type', PostTypeEnum::FREE)
                     ->orWhere( function($q2) use(&$viewer) {
                         $q2->where('type', PostTypeEnum::PRICED)
                            ->whereHas('sharees', function($q3) use(&$viewer) {
                                $q3->where('id', $viewer->id);
                            });
                     });
              });

        $posts = $query->get();
        return $posts;
    }

    // get free + post-purchased-by-user + subscriber-only by timeline (eg: as fan viewing subscribed timeline)
    public static function getSubscriberFeed(Timeline $timeline, User $viewer) : Collection
    {
        $query = Post::query();

        if ( !$timeline->subscribers->contains($viewer->id) ) {
            abort(403); // ] assumes viewer is a subscriber!
        }
        $query->where('postable_type', 'timelines')
              ->where('postable_id', $timeline->id)
              ->where( function($q1) use(&$viewer) {
                  $q1->where('type', PostTypeEnum::FREE)
                     ->orWhere('type', PostTypeEnum::SUBSCRIBER)
                     ->orWhere( function($q2) use(&$viewer) {
                         $q2->where('type', PostTypeEnum::PRICED)
                            ->whereHas('sharees', function($q3) use(&$viewer) {
                                $q3->where('id', $viewer->id);
                            });
                     });
              });

        $posts = $query->get();
        return $posts;
    }

}
