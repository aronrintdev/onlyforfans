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
use Illuminate\Support\Facades\Log;

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

        try {
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
        } catch (\Exception $e) {
            Log::error($e);
        }

        try {
            $timelines = Timeline::search($query)->paginate( $request->input('take', 10) );
        } catch (\Exception $e) {
            $timelines = [];
            Log::error($e);
        }

        try {
            $posts = Post::search($query)->paginate($request->input('take', 10));
        } catch (\Exception $e) {
            $posts = [];
            Log::error($e);
        }

        try {
            $tags = Contenttag::search($query)->paginate($request->input('take', 10));
        } catch (\Exception $e) {
            $tags = [];
            Log::error($e);
        }

        return [
            'timelines' => new TimelineCollection($timelines),
            'posts' => new PostCollection($posts),
            'tags' => new ContenttagCollection($tags),
        ];
    }
}
