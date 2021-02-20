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
use App\Models\Post;
use App\Models\User;

class FeedsController extends AppBaseController
{
    public function home(Request $request)
    {
        // posts from all followed timelines
        $sessionUser = $request->user();
        $query = Post::query();
        $query->whereHas('timeline', function($q1) use(&$sessionUser) {
            $q1->whereHas('followers', function($q2) use(&$sessionUser) {
                $q2->where('id', $sessionUser->id);
            });
        });
        $posts = $query->get();
        return response()->json([
            'feeditems' => $posts,
        ]);
    }

    public function show(Request $request, Timeline $feed)
    {
        // posts from specific timeline (feed)
        $timeline = $feed;
        $this->authorize('view', $timeline); // must be follower or subscriber
        $query = Post::query();
        $query->where('postable_type', 'timelines')->where('postable_id', $timeline->id);
        $posts = $query->get();
        return response()->json([
            'feeditems' => $posts,
        ]);
    }

}
