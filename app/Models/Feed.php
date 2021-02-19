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


    // 

    // *as owner viewing home, get free for timelines I follow + purchased-only for posts I've purchased + subscriber-only for timelines I subscribe to
    public static function getHomeFeed(User $user, bool $isStrict=false) : Collection
    {
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
    }

    // get free + post-purchased-by-user + subscriber-only by timeline (eg: as fan viewing subscribed timeline)
    public static function getSubscriberFeed(Timeline $timeline, User $viewer) : Collection
    {
    }


    // ---
    public static function getByTimeline(Timeline $timeline, User $viewer, bool $isStrict=true) : Collection
    {
        $query = Post::query();

        $query->where('postable_type', 'timelines')
              ->where('postable_id', $timeline->id);

        if ($isStrict) {
            if ( $timeline->subscribers->contains($viewer->id) ) {
                $query->where('postable_type', 'timelines')
                      ->where('postable_id', $timeline->id)
                      ->where( function($q1) use(&$viewer) {
                          $q1->where('type', PostTypeEnum::FREE)
                             ->orWhere('type', PostTypeEnum::SUBSCRIBER)
                             ->orWhere( function($q2) use(&$viewer) {
                                 $q2->where('type', PostTypeEnum::PRICED)
                                    ->whereHas('sharees', function($q3) use(&$viewer) {
                                        $q3->where('id', $viewer->id);
                                        //$q3->where('sharee_id', $viewer->id);
                                    });
                             });
                      });
            } else {
                $query->where('postable_type', 'timelines')
                      ->where('postable_id', $timeline->id)
                      ->where( function($q1) use(&$viewer) {
                          $q1->where('type', PostTypeEnum::FREE)
                             ->orWhere( function($q2) use(&$viewer) {
                                 $q2->where('type', PostTypeEnum::PRICED)
                                    ->whereHas('sharees', function($q3) use(&$viewer) {
                                        $q3->where('id', $viewer->id);
                                        //$q3->where('sharee_id', $viewer->id);
                                    });
                             });
                      });
            }
        }

        $posts = $query->get();
        return $posts;
    }

}
