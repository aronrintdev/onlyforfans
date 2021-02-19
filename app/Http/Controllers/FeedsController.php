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

    // /home
    public function home(Request $request)
    {
        $timeline = $request->user()->timeline;
        return response()->json([
            'feeditems' => $posts,
        ]);
    }

    // show timeline for a user's feed, eg : /{username}
    public function show(Request $request, Timeline $feed)
    {
        $timeline = $feed;
        $this->authorize('view', $timeline);
        $sessionUser = $request->user();

        $query = Post::query();
        $query->where('postable_type', 'timelines')
              ->where('postable_id', $timeline->id)
              ->where( function($q1) use(&$sessionUser) {
                  $q1->where('type', PostTypeEnum::FREE)
                     ->orWhere( function($q2) use(&$sessionUser) {
                         $q2->where('type', PostTypeEnum::PRICED)
                            ->whereHas('sharees', function($q3) use(&$sessionUser) {
                                $q3->where('id', $sessionUser->id);
                            });
                     });
              });

        $posts = $query->get();
        //dd($posts, $timeline->id, $timeline->posts->count());

        return response()->json([
            'feeditems' => $posts,
        ]);
    }

    // /me
    public function me(Request $request)
    {
        $timeline = $request->user()->timeline;
        return response()->json([
            'feeditems' => $posts,
        ]);
    }
}
