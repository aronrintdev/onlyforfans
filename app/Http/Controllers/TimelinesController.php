<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

use App\Http\Resources\MediafileCollection;
use App\Http\Resources\PostCollection;
use App\Http\Resources\Timeline as TimelineResource;
use App\Libs\FeedMgr;
use App\Libs\UserMgr;

use App\Models\Setting;
use App\Models\Timeline;
use App\Models\Fanledger;
use App\Models\Mediafile;
use App\Models\Post;
use App\Models\User;

use App\Enums\PaymentTypeEnum;
use App\Enums\MediafileTypeEnum;
use App\Enums\PostTypeEnum;

class TimelinesController extends AppBaseController
{
    public function index(Request $request)
    {
        $query = User::query();

        // Apply filters
        //  ~ %TODO

        return response()->json([
            'users' => $query->get(),
        ]);
    }

    public function show(Request $request, Timeline $timeline)
    {
        $this->authorize('view', $timeline);
        //$timeline->load(['avatar', 'cover']);
        //$timeline->userstats = $request->user()->getStats();
        //return [ 'timeline' => $timeline, ];
        return new TimelineResource($timeline);
    }

    // Display my home timeline
    public function homefeed(Request $request)
    {
        $sessionUser = request()->user();
        $query = Post::with('mediafiles', 'user')->withCount(['comments', 'likes'])->where('active', 1);
        $query->homeTimeline()->sort( $request->input('sortBy', 'default') );
        // %NOTE: we could also just remove post-query, as the feed will auto-update to fill length of page (?)
        if ( $request->boolean('hideLocked') ) {
            $query->where( function($q1) use(&$sessionUser) {
                $q1->where('type', PostTypeEnum::FREE)
                   ->orWhere( function($q2) use(&$sessionUser) {
                       $q2->where('type', PostTypeEnum::PRICED)
                          ->whereDoesntHave('sharees', function($q3) use(&$sessionUser) {
                              $q3->where('users.id', $sessionUser->id);
                          });
                       $q2->where('type', PostTypeEnum::SUBSCRIBER)
                          ->whereDoesntHave('timeline.subscribers', function($q3) use(&$sessionUser) {
                              $q3->where('users.id', $sessionUser->id);
                          });
                   });
            });
        }
        if ( $request->boolean('hidePromotions') ) {
        }
        $data = $query->paginate( $request->input('take', env('MAX_POSTS_PER_REQUEST', 10)) );
        return new PostCollection($data);
    }

    // Get a list of items that make up a timeline feed, typically posts but
    //  keep generic as we may want to throw in other things
    //  %TODO: 
    //  ~ [ ] trending tags
    //  ~ [ ] announcements
    //  ~ [ ] hashtag search
    public function feed(Request $request, Timeline $timeline)
    {
        //$this->authorize('view', $timeline); // must be follower or subscriber
        //$filters = [];
        $query = Post::with('mediafiles', 'user')->withCount(['comments', 'likes'])->where('active', 1);
        $query->byTimeline($timeline->id)->sort( $request->input('sortBy', 'default') );
        $data = $query->paginate( $request->input('take', env('MAX_POSTS_PER_REQUEST', 10)) );
        return new PostCollection($data);
    }

    // 'Photos' Feed
    // %TODO: move to mediafiles controller (?)
    public function photos(Request $request, Timeline $timeline)
    {
        $query = Mediafile::with('resource')
            ->whereIn('mimetype', ['image/jpeg', 'image/jpg', 'image/png'])
            ->where('mftype', MediafileTypeEnum::POST);
        //$query->byTimeline($timeline->id)->sort( $request->input('sortBy', 'default') );
        $query->whereHasMorph( 'resource', [Post::class], function($q1) use(&$timeline) {
            $q1->where('postable_type', 'timelines')->where('postable_id', $timeline->id);
        });
        $data = $query->paginate( $request->input('take', env('MAX_POSTS_PER_REQUEST', 10)) );
        return new MediafileCollection($data);
    }

    // Get suggested users (list/index)
    public function suggested(Request $request)
    {
        $TAKE = $request->input('take', 5);
        $followedIDs = $request->user()->followedtimelines->pluck('id');
        $query = Timeline::with(['user', 'avatar', 'cover'])->inRandomOrder();
        $query->whereHas('user', function($q1) use(&$request, &$followedIDs) {
            $q1->where('id', '<>', $request->user()->id); // skip myself
            // skip timelines I'm already following
            $q1->whereDoesntHave('followedtimelines', function($q2) use(&$followedIDs) {
                $q2->whereIn('shareable_id', $followedIDs);
            });
        });

        // Apply filters
        //  ~ %TODO

        return response()->json([
            'timelines' => $query->take($TAKE)->get(),
        ]);
    }

    // %FIXME: better way to do this is to pull down a set of mediafiles associated with posts on this timeline (?)
    public function previewPosts(Request $request, Timeline $timeline)
    {
        $TAKE = $request->input('take', 6);
        $query = Post::with('mediafiles', 'user')
            ->has('mediafiles')
            ->withCount('comments')->orderBy('comments_count', 'desc')
            //->withCount('likes')->orderBy('likes_count', 'desc')
            ->where('active', 1);
        $query->where('postable_type', 'timelines')->where('postable_id', $timeline->id);
        $data = $query->take($TAKE)->latest()->get();
        return new PostCollection($data);
    }


    // toggles, returns set state
    public function follow(Request $request, Timeline $timeline)
    {
        $this->authorize('follow', $timeline);

        $request->validate([
            'sharee_id' => 'required|uuid|exists:users,id',
        ]);
        if ( $request->sharee_id != $request->user()->id ) {
            abort(403);
        }

        $cattrs = [];
        if ( $request->has('notes') ) {
            $cattrs['notes'] = $request->notes;
        }

        $existing = $timeline->followers->contains($request->sharee_id); // currently following?

        if ($existing) {
            $timeline->followers()->detach($request->sharee_id);
            $isFollowing = false;
        } else {
            $timeline->followers()->attach($request->sharee_id, [ // will sync work here?
                'shareable_type' => 'timelines',
                'shareable_id' => $timeline->id,
                'is_approved' => 1, // %FIXME
                'access_level' => 'default',
                'cattrs' => json_encode($cattrs),
            ]); //
            $isFollowing = true;
        }

        $timeline->refresh();
        return response()->json([
            'is_following' => $isFollowing,
            'timeline' => $timeline,
            'follower_count' => $timeline->followers->count(),
        ]);
    }

    public function subscribe(Request $request, Timeline $timeline)
    {
        $this->authorize('follow', $timeline);

        $request->validate([
            'sharee_id' => 'required|uuid|exists:users,id',
        ]);
        if ( $request->sharee_id != $request->user()->id ) {
            abort(403);
        }

        list($timeline, $isSubscribed) = DB::transaction( function() use(&$timeline, &$request) {
            $cattrs = [];
            if ( $request->has('notes') ) {
                $cattrs['notes'] = $request->notes;
            }

            $existingFollowing = $timeline->followers->contains($request->sharee_id); // currently following?
            $existingSubscribed = $timeline->subscribers->contains($request->sharee_id); // currently subscribed?

            if ( $existingSubscribed ) {
                // unsubscribe & unfollow
                $timeline->followers()->detach($request->sharee_id);
                $isSubscribed = false;
            } else {
                if ( $existingFollowing ) {
                    // upgrade from follow to subscribe => remove existing (covers 'upgrade') case
                    $timeline->followers()->detach($request->sharee_id); 
                } // otherwise, just a direct subscription...
                $timeline->followers()->attach($request->sharee_id, [
                    'shareable_type' => 'timelines',
                    'shareable_id' => $timeline->id,
                    'is_approved' => 1, // %FIXME
                    'access_level' => 'premium',
                    'cattrs' => json_encode($cattrs), // %FIXME: add a observer function?
                ]); //
                $timeline->receivePayment(
                    PaymentTypeEnum::SUBSCRIPTION,
                    $request->user(),
                    $timeline->price,
                    $cattrs,
                );
                $isSubscribed = true;
            }
            return [$timeline, $isSubscribed];
        });

        $timeline->refresh();
        return response()->json([
            'is_subscribed' => $isSubscribed,
            'timeline' => $timeline,
            'subscriber_count' => $timeline->subscribers->count(),
        ]);
    }

    public function tip(Request $request, Timeline $timeline)
    {
        $request->validate([
            'base_unit_cost_in_cents' => 'required|numeric',
        ]);

        $cattrs = [];
        if ( $request->has('notes') ) {
            $cattrs['notes'] = $request->notes;
        }

        try {
            $timeline->receivePayment(
                PaymentTypeEnum::TIP,
                $request->user(),
                $request->base_unit_cost_in_cents,
                $cattrs,
            );
        } catch(Exception | Throwable $e) {
            return response()->json([ 'message'=>$e->getMessage() ], 400);
        }

        $timeline->refresh();
        return response()->json([
            'timeline' => $timeline,
        ]);
    }

}
