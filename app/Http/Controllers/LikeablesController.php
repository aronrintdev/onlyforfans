<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Likeable as LikeableModel;
use App\Models\User;
use App\Models\Comment;
use App\Models\Mediafile;
use App\Models\Post;
use App\Models\Story;
use App\Interfaces\Likeable;
use App\Notifications\ResourceLiked;
use App\Http\Resources\LikeableCollection;

class LikeablesController extends AppBaseController
{
    // %TODO: distinguish returning resources user has liked vs liked resources owned by user (!)
    public function index(Request $request)
    {
        $request->validate([
            // filters
            'likeable_type' => 'string|in: posts, comments, mediafiles',
            'liker_id' => 'uuid|exists:users,id', // if admin only
        ]);
        $filters = $request->only(['likeable_type', 'likeable_id']) ?? [];

        // Init query
        //$query = LikeableModel::query();
        $query = LikeableModel::with(['liker', 'likeable']);

        // Check permissions
        if ( !$request->user()->isAdmin() ) {
            // non-admin: can only view own...
            $query->whereHasMorph(
                'likeable',
                [Post::class, Comment::class, Story::class],
                function (Builder $q1, $type) use(&$request) {
                    switch ($type) {
                        case Post::class:
                        case Comment::class:
                        case Story::class:
                        case Mediafile::class:
                            $column = 'user_id';
                            break;
                        default:
                            throw new Exception('Invalid morphable type for Likeable: '.$type);
                    }
                    $q1->where($column, $request->user()->id);
                }
            );
        }

        // Apply any filters
        foreach ($filters as $key => $f) {
            switch ($key) {
            default:
                $query->where($key, $f);
                break;
            }
        }

        $data = $query->paginate( $request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)) );
        return new LikeableCollection($data);
    }

    // %TODO: remove liker param and just use session user for liker (?)
    // %TODO: notify user
    // %TODO: better architecture would be:
    //  ~ addLike(), removeLike(): each of which handle all resources...OR...
    //  ~ addPostLike() + removePostLike | addCommentLike() + removeCommentLike(), etc
    //     This latter may be better, as the UI context generally *knows* what resource
    //     is being liked and thus it's cleaner to encode that in the URL itself. We can also
    //     then use DI on the $likeable which makes more sense
    // to use an arg like Likeable $likeable, replacing getMorphedModel below, see
    //  ~ https://www.reddit.com/r/laravel/comments/ai0x6w/polymorphic_route_model_binding/eek37vs
    //  ~ https://laravel.com/docs/8.x/routing#route-model-binding
    // %FIXME: are liker and session_user always the same (?) -> remove liker field just use session user
    // %FIXME: should probably be store not update
    public function update(Request $request, User $liker)
    {
        $request->validate([
            'likeable_type' => 'required|string|alpha-dash|in:comments,mediafiles,posts,stories',
            'likeable_id' => 'required|uuid',
        ]);

        $alias = $request->likeable_type;
        $model = Relation::getMorphedModel($alias); // string
        $likeable = (new $model)->where('id', $request->likeable_id)->firstOrFail();
//dd($likeable, $model, $alias);

        if ($request->user()->cannot('like', $likeable)) {
            abort(403);
        }

        $likeable->likes()->syncWithoutDetaching($liker->id); // %NOTE!! %TODO: apply elsewhere instead of attach

        switch ($alias) {
            case 'posts':
            case 'comments':
                $likeable->user->notify(new ResourceLiked($likeable, $liker)); // owner is relation 'user'
            default:
        }

        return response()->json([
            'likeable' => $likeable,
            'like_count' => $likeable->likes->count(),
        ]);
    }

    // %FIXME: not really REST-ful (ie likee param should be likeable, but not sure that makes sense in the UI)
    // ~ https://stackoverflow.com/questions/299628/is-an-entity-body-allowed-for-an-http-delete-request
    public function destroy(Request $request, User $liker)
    {
        $request->validate([
            'likeable_type' => 'required|string|alpha-dash|in:comments,mediafiles,posts,stories',
            'likeable_id' => 'required|uuid',
        ]);

        $alias = $request->likeable_type;
        $model = Relation::getMorphedModel($alias);
        $likeable = (new $model)->where('id', $request->likeable_id)->firstOrFail();

        if ($request->user()->cannot('like', $likeable)) {
            abort(403);
        }

        $likeable->likes()->detach($liker->id);

        return response()->json([
            'like_count' => $likeable->likes->count(),
        ]);
    }
}
