<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Http\Resources\BookmarkCollection;
use App\Http\Resources\Bookmark as BookmarkResource;
use App\Models\User;
use App\Models\Bookmark;

class BookmarksController extends AppBaseController
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
        $query = Bookmark::with(['user', 'bookmarkable']);

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
        return new BookmarkCollection($data);
    }

    public function show(Request $request, Bookmark $bookmark)
    {
        $this->authorize('view', $bookmark);
        return new BookmarkResource($bookmark);
    }

    public function store(Request $request)
    {
        $request->validate([
            'bookmarkable_id' => 'required|uuid',
            'bookmarkable_type' => 'required|string|alpha-dash|in:posts',
            //'user_id' => 'required|uuid|exists:users,id',
        ]);

        $alias = $request->bookmarkable_type;
        $model = Relation::getMorphedModel($alias);
        $bookmarkable = (new $model)->where('id', $request->bookmarkable_id)->firstOrFail();

        //$this->authorize('view', $bookmarkable);
        if ($request->user()->cannot('bookmark', $bookmarkable)) {
            abort(403);
        }

        $bookmarker = $request->user();
        $bookmark = Bookmark::create([
            'bookmarkable_id' => $request->bookmarkable_id,
            'bookmarkable_type' => $request->bookmarkable_type,
            'user_id' => $bookmarker->id,
        ]);
        return new BookmarkResource($bookmark);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'bookmarkable_id' => 'required|uuid',
            'bookmarkable_type' => 'required|string|alpha-dash|in:posts',
        ]);
        $bookmark = Bookmark::where('bookmarkable_id', $request->bookmarkable_id)
                            ->where('bookmarkable_type', $request->bookmarkable_type)
                            ->first();
        if (!$bookmark) {
            abort(404);
        }
        $this->authorize('delete', $bookmark);
        $bookmark->delete();
        return response()->json([]);
    }

    public function destroy(Request $request, Bookmark $bookmark)
    {
        $this->authorize('delete', $bookmark);
        $bookmark->delete();
        return response()->json([]);
    }
}
