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

    // Display my home timeline
    public function home(Request $request)
    {
        $sessionUser = Auth::user();
        return view('timelines.home', [
            'sessionUser' => $sessionUser,
            //'myVault' => $myVault,
            //'vaultRootFolder' => $vaultRootFolder,
        ]);
    }

    // Get a list of items that make up a timeline feed, typically posts but
    //  keep generic as we may want to throw in other things
    public function feeditems(Request $request, $timelineID)
    {
        $sessionUser = Auth::user();

        $timeline = ( $timelineID === 'home' ) 
            ? $sessionUser->timeline
            : null;

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

        $feeditems = FeedMgr::getPosts($sessionUser, $filters, $page, $take); // %TODO: work with L5.8 pagination
        //$feeditems = FeedMgr::getPostsRaw($sessionUser, $filters);

        return response()->json([
            'sessionUser' => $sessionUser,
            'feeditems' => $feeditems,
            'timeline' => $timeline,
        ]);
    }

}
