<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Throwable;
use App\Models\User;
use App\Libs\FeedMgr;
use App\Libs\UserMgr;

use App\Models\Setting;
use App\Models\Timeline;
use App\Models\Fanledger;

use Illuminate\Http\Request;
use App\Enums\PaymentTypeEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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

    // Get suggested users (list/index)
    public function suggested(Request $request)
    {
        $TAKE = $request->input('take', 5);

        $sessionUser = Auth::user();
        $followedIDs = $sessionUser->followedtimelines->pluck('id');

        $query = Timeline::with('user')->inRandomOrder();
        $query->whereHas('user', function($q1) use(&$sessionUser, &$followedIDs) {
            $q1->where('id', '<>', $sessionUser->id); // skip myself
            // skip timelines I'm already following
            $q1->whereHas('followedtimelines', function($q2) use(&$followedIDs) {
                $q2->whereNotIn('timeline_id', $followedIDs);
            });
        });

        // Apply filters
        //  ~ %TODO

        return response()->json([
            'timelines' => $query->take($TAKE)->get(),
        ]);
    }

    public function show(Request $request, $username)
    {
        //$timeline = Timeline::with('user')->where('username', $username)->firstOrFail();
        $timeline = Timeline::with('user')->whereHas('user', function($q1) use($username) {
            $q1->where('username', $username);
        })->first();
        $sales = Fanledger::where('seller_id', $timeline->user->id)->sum('total_amount');

        $timeline->userstats = [ // %FIXME DRY
            'post_count' => $timeline->posts->count(),
            'like_count' => 0, // %TODO $timeline->user->postlikes->count(),
            'follower_count' => $timeline->followers->count(),
            'following_count' => $timeline->user->followedtimelines->count(),
            'subscribed_count' => 0, // %TODO $sessionUser->timeline->subscribed->count()
            'earnings' => $sales,
        ];

        return view('timelines.show', [
            'sessionUser' => $request->user(),
            'timeline' => $timeline,
        ]);
    }

    // Display my home timeline
    public function home(Request $request)
    {
        $timeline = $request->user()->timeline()->with('user')->first();
        $sales = Fanledger::where('seller_id', $timeline->user->id)->sum('total_amount');

        $timeline->userstats = [ // %FIXME DRY
            'post_count' => $timeline->posts->count(),
            'like_count' => 0, // %TODO $timeline->user->postlikes->count(),
            'follower_count' => $timeline->followers->count(),
            'following_count' => $timeline->user->followedtimelines->count(),
            'subscribed_count' => 0, // %TODO $sessionUser->timeline->subscribed->count()
            'earnings' => $sales,
        ];

        return view('timelines.home', [
            'sessionUser' => $request->user(),
            'timeline' => $timeline,
            //'myVault' => $myVault,
            //'vaultRootFolder' => $vaultRootFolder,
        ]);
    }

    // Get a list of items that make up a timeline feed, typically posts but
    //  keep generic as we may want to throw in other things
    public function feeditems(Request $request, Timeline $timeline)
    {
        $sessionUser = Auth::user();
        $follower = $timeline->user;
        $page = $request->input('page', 1);
        $take = 5; // $request->input('take', Setting::get('items_page'));

        // %TODO
        //  ~ [ ] trending tags
        //  ~ [ ] announcements
        //  ~ [ ] 

        $filters = [];

        if ($request->hashtag) {
            $filters['hashtag'] = '#'.$request->hashtag;
        }

        $feeditems = FeedMgr::getPosts($follower, $filters, $page, $take); // %TODO: work with L5.8 pagination
        //$feeditems = FeedMgr::getPostsRaw($sessionUser, $filters);

        return response()->json([
            'feeditems' => $feeditems,
        ]);
    }

    public function follow(Request $request, Timeline $timeline)
    {
        $this->authorize('view', $timeline);

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

        $timeline->followers()->detach($request->sharee_id); // remove existing
        $timeline->followers()->attach($request->sharee_id, [ // will sync work here?
            'shareable_type' => 'timelines',
            'shareable_id' => $timeline->id,
            'is_approved' => 1, // %FIXME
            'access_level' => 'default',
            'cattrs' => json_encode($cattrs),
        ]); //

        $timeline->refresh();
        return response()->json([
            'timeline' => $timeline,
            'follower_count' => $timeline->followers->count(),
        ]);
    }

    public function subscribe(Request $request, Timeline $timeline)
    {
        $sessionUser = Auth::user(); // subscriber (purchaser)

        $request->validate([
            'sharee_id' => 'required|uuid|exists:users,id',
        ]);
        if ( $request->sharee_id != $sessionUser->id ) {
            abort(403);
        }

        $timeline = DB::transaction( function() use(&$timeline, &$request, &$sessionUser) {
            $cattrs = [];
            if ( $request->has('notes') ) {
                $cattrs['notes'] = $request->notes;
            }
            $timeline->followers()->detach($request->sharee_id); // remove existing (covers 'upgrade') case
            $timeline->followers()->attach($request->sharee_id, [
                'shareable_type' => 'timelines',
                'shareable_id' => $timeline->id,
                'is_approved' => 1, // %FIXME
                'access_level' => 'premium',
                'cattrs' => json_encode($cattrs), // %FIXME: add a observer function?
            ]); //
            $timeline->receivePayment(
                PaymentTypeEnum::SUBSCRIPTION,
                $sessionUser,
                $timeline->user->price*100, // %FIXME: should be on timeline
                $cattrs,
            );
            return $timeline;
        });

        $timeline->refresh();
        return response()->json([
            'timeline' => $timeline,
            'follower_count' => $timeline->followers->count(),
        ]);
    }

    public function tip(Request $request, Timeline $timeline)
    {
        $sessionUser = Auth::user(); // sender of tip (purchaser)

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
                $sessionUser,
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
