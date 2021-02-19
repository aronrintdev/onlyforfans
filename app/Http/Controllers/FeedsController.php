<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

use App\Enums\PostTypeEnum;
use App\Models\Setting;
use App\Models\Timeline;
use App\Models\Feed;
use App\Models\Post;
use App\Models\User;

class FeedsController extends AppBaseController
{
    public function home(Request $request)
    {
        $posts = Feed::getHomeFeed($request->user());
        return response()->json([
            'feeditems' => $posts,
        ]);
    }

    public function show(Request $request, Timeline $feed)
    {
        $timeline = $feed;
        $this->authorize('view', $timeline); // must be follower or subscriber

        $isSubscriber = $timeline->subscribers->contains($request->user()->id);
        $posts = $isSubscriber
            ? Feed::getSubscriberFeed($timeline, $request->user())
            : Feed::getFollowerFeed($timeline, $request->user());

        return response()->json([
            'feeditems' => $posts,
        ]);
    }

    public function me(Request $request)
    {
        $timeline = $request->user()->timeline;
        return response()->json([
            'feeditems' => $posts,
        ]);
    }

    public function getPublic(Request $request, Timeline $feed)
    {
        $posts = Feed::getPublicFeed($feed);
        return response()->json([
            'feeditems' => $posts,
        ]);
    }
}
