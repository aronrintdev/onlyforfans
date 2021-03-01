<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostCollection;
use App\Http\Resources\StoryCollection;
use App\Http\Resources\TimelineCollection;
use App\Models\Post;
use App\Models\Story;
use App\Models\Timeline;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Handle search requests
 */
class SearchController extends Controller
{
    /**
     * Basic search endpoint
     */
    public function search(Request $request)
    {
        //

        // TODO: Dummy Data | Replace with real search
        $timelines = Timeline::latest()->paginate( $request->input('take', 10) );
        $posts     = Post::latest()->paginate( $request->input('take', 10) );
        $stories   = Story::latest()->paginate( $request->input('take', 10) );
        return [
            'timelines' => new TimelineCollection($timelines),
            'posts' => new PostCollection($posts),
            'stories' => new StoryCollection($stories),
        ];
    }
}
