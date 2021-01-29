<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Throwable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

use App\Setting;
use App\Libs\UserMgr;
use App\Libs\FeedMgr;

use App\Fanledger;
use App\Timeline;
//use App\Enums\PaymentTypeEnum;

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
        $sessionUser = Auth::user();
        $timeline = Timeline::with('user')->where('username', $username)->firstOrFail();
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
            'sessionUser' => $sessionUser,
            'timeline' => $timeline,
        ]);
    }

    // Display my home timeline
    public function home(Request $request)
    {
        $sessionUser = Auth::user();
        $timeline = $sessionUser->timeline()->with('user')->first();
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
            'sessionUser' => $sessionUser,
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

}
