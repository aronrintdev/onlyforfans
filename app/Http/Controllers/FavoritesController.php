<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Http\Resources\FavoriteCollection;
use App\Http\Resources\Favorite as FavoriteResource;
use App\Models\User;
use App\Models\Favorite;

class FavoritesController extends AppBaseController
{
    public function index(Request $request)
    {
        $request->validate([
            'filters' => 'array',
            'filters.post_id' => 'uuid|exists:posts,id', // if admin or post-owner only (per-post comments by fan use posts controller)
            'filters.user_id' => 'uuid|exists:users,id', // if admin only
        ]);

        $filters = $request->filters ?? [];

        // Init query
        $query = Favorite::with(['user', 'favoritable']);

        // Check permissions
        if ( !$request->user()->isAdmin() ) {

            // non-admin: only view own comments
            $query->where('user_id', $request->user()->id); 
            unset($filters['user_id']);

            if ( array_key_exists('post_id', $filters) ) {
                $post = Post::find($filters['post_id']);
                $this->authorize('update', $post); // non-admin must own post filtered on
            }
        }

        // Apply any filters
        foreach ($filters as $key => $f) {
            // %TODO: subgroup under 'filters' (need to update axios.GET calls as well in Vue)
            switch ($key) {
                case 'user_id':
                case 'post_id':
                    $query->where($key, $f);
                    break;
            }
        }

        $data = $query->paginate( $request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)) );
        return new FavoriteCollection($data);
    }

    public function show(Request $request, Favorite $favorite)
    {
        $this->authorize('view', $favorite);
        return new FavoriteResource($favorite);
    }

    public function store(Request $request)
    {
        $request->validate([
            'favoritable_id' => 'required|uuid',
            'favoritable_type' => 'required|string|alpha-dash|in:posts,mediafiles,timelines,stories',
            //'user_id' => 'required|uuid|exists:users,id',
        ]);

        $alias = $request->favoritable_type;
        $model = Relation::getMorphedModel($alias);
        $favoritable = (new $model)->where('id', $request->favoritable_id)->firstOrFail();

        //$this->authorize('view', $favoritable);
        if ($request->user()->cannot('favorite', $favoritable)) {
            abort(403);
        }

        $favoriteer = $request->user();
        $favorite = Favorite::create([
            'favoritable_id' => $request->favoritable_id,
            'favoritable_type' => $request->favoritable_type,
            'user_id' => $favoriteer->id,
        ]);
        return new FavoriteResource($favorite);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'favoritable_id' => 'required|uuid',
            'favoritable_type' => 'required|string|alpha-dash|in:posts,mediafiles,timelines,stories',
        ]);
        $favorite = Favorite::where('favoritable_id', $request->favoritable_id)
                            ->where('favoritable_type', $request->favoritable_type)
                            ->first();
        if (!$favorite) {
            abort(404);
        }
        $this->authorize('delete', $favorite);
        $favorite->delete();
        return response()->json([]);
    }

    public function destroy(Request $request, Favorite $favorite)
    {
        $this->authorize('delete', $favorite);
        $favorite->delete();
        return response()->json([]);
    }
}
