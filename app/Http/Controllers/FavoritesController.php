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
            'user_id' => 'uuid|exists:users,id', // only admin can filter by user
            'favoritable_type' => 'string|alpha-dash|in:posts,mediafiles,timelines,stories',
        ]);
        $filters = $request->only( ['user_id', 'favoritable_type'] ) ?? [];

        $query = Favorite::with(['user', 'favoritable']); // Init query

        // Check permissions
        if ( !$request->user()->isAdmin() ) {
            // non-admin: can only view own ...
            $query->where('user_id', $request->user()->id); 
            unset($filters['user_id']);
        }

        // Apply any filters
        foreach ($filters as $key => $f) {
            switch ($key) {
            case 'favoritable_type':
                if ( $f==='timelines' ) {
                    $query->with(['favoritable.cover', 'favoritable.avatar']);
                } else if ( $f==='mediafiles' ) {
                    $query->with(['favoritable.resource']); 
                }
                $query->where('favoritable_type', $f);
                break;
            default:
                $query->where($key, $f);
            }
        }

        /*
        if ( $request->has('favoritable_type') ) {
            switch ($request->favoritable_type) {
            case 'timelines':
                $query->with(['favoritable.cover', 'favoritable.avatar']);
                break;
            case 'mediafiles':
                $query->with(['favoritable.resource']); 
                break;
            }
            $query->where('favoritable_type', $request->favoritable_type);
        }
         */

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
