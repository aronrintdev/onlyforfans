<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostCollection;
use App\Http\Resources\ContenttagCollection;
use App\Http\Resources\TimelineCollection;
use App\Models\Post;
use App\Models\Contenttag;
use App\Models\Timeline;
use App\Models\User;
use Illuminate\Http\Request;
use MeiliSearch\Client;

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
        $params = $request->all();
        $query = $params['query'] ?? $params['q'];

        $client = new Client(env('MEILISEARCH_HOST'), env('MEILISEARCH_KEY'));
        $indexes =['timelines_index', 'posts_index', 'contenttags_index'];
        foreach($indexes as $index) {
            $attributes = $client->index($index)->getSearchableAttributes();
            $attributes = array_diff($attributes, array("id"));
            $client->index($index)->updateSearchableAttributes($attributes);
        }

        $timelines = Timeline::search($query)->paginate( $request->input('take', 10) );
        $posts     = Post::search($query)->paginate( $request->input('take', 10) );
        $tags      = Contenttag::search($query)->paginate( $request->input('take', 10) );
        return [
            'timelines' => new TimelineCollection($timelines),
            'posts' => new PostCollection($posts),
            'tags' => new ContenttagCollection($tags),
        ];
    }
}
