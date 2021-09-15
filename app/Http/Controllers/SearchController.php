<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use MeiliSearch\Client;
use App\Models\Timeline;
use App\Models\Contenttag;
use Illuminate\Http\Request;
use App\Http\Resources\PostCollection;
use Illuminate\Support\Facades\Config;
use App\Http\Resources\TimelineCollection;
use App\Http\Resources\ContenttagCollection;

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

        $client = new Client(Config::get('scout.meilisearch.host'), Config::get('scout.meilisearch.key'));
        $client->index('posts_index')->updateSearchableAttributes([
            'description'
        ]);
        $client->index('timelines_index')->updateSearchableAttributes([
            'name',
            'slug',
            'username'
        ]);
        $client->index('contenttags_index')->updateSearchableAttributes([
            'ctag'
        ]);

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
