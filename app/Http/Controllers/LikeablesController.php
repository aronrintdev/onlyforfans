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
use App\Interfaces\Likeable;
use App\Notifications\ResourceLiked;

class LikeablesController extends AppBaseController
{
    // %TODO: distinguish returning resources user has liked vs liked resources owned by user (!)
    public function index(Request $request)
    {
        $request->validate([
            // filters
            'likeable_type' => 'string|in:posts,comments, mediafiles',
            'liker_id' => 'uuid|exists:users,id', // if admin only
        ]);

        // Init query
        $query = LikeableModel::query();

        // Check permissions
        if ( !$request->user()->isAdmin() ) {

            // non-admin: only view own...
            //$query->where('user_id', $request->user()->id); 
            //$query->take(5);
            /*
            $query->whereHas('posts', function($q1) {
                $q1->
            });
             */
            $query->whereHasMorph(
                'likeable',
                [Post::class, Comment::class],
                function (Builder $q1, $type) use(&$request) {
                    switch ($type) {
                        case Post::class:
                        case Comment::class:
                            $column = 'user_id';
                            break;
                        default:
                            throw new Exception('Invalid morphable type for Likeable: '.$type);
                    }
                    $q1->where($column, $request->user()->id);
                }
            );

            //unset($filters['user_id']);

            /*
            if ( array_key_exists('post_id', $filters) ) {
                $post = Post::find($filters['post_id']);
                $this->authorize('update', $post); // non-admin must own post filtered on
            }
             */
        }

        /*
        // Apply any filters
        foreach ($filters as $key => $f) {
            // %TODO: subgroup under 'filters' (need to update axios.GET calls as well in Vue)
            switch ($key) {
                case 'user_id':
                case 'post_id':
                case 'parent_id':
                    $query->where($key, $f);
                    break;
            }
        }
         */

        //$data = $query->paginate( $request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)) );
        //return new LikeableCollection($data);

        $data = $query->get();
        dd($data);
        return response()->json([
            'likeables' => $data,
        ]);
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
        $model = Relation::getMorphedModel($alias);
        $likeable = (new $model)->where('id', $request->likeable_id)->firstOrFail();

        if ($request->user()->cannot('like', $likeable)) {
            abort(403);
        }

        $likeable->likes()->syncWithoutDetaching($liker->id); // %NOTE!! %TODO: apply elsewhere instead of attach

        $liker->notify(new ResourceLiked($likeable));

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
