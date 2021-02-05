<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\User;
use App\Interfaces\Likeable;

class LikeablesController extends AppBaseController
{
    public function index(Request $request)
    {
        $likeables = collect(); // %TODO
        return response()->json([
            'likeables' => $likeables,
        ]);
    }

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
    public function update(Request $request, User $likee)
    {
        $request->validate([
            'likeable_type' => 'required|string|alpha-dash|in:comments,mediafiles,posts,stories',
            'likeable_id' => 'required|numeric|min:1',
        ]);

        $alias = $request->likeable_type;
        $model = Relation::getMorphedModel($alias);
        $likeable = (new $model)->where('id', $request->likeable_id)->firstOrFail();

        if ($request->user()->cannot('like', $likeable)) {
            //dd('here', $likee, $likeable);
            abort(403);
        }

        $likeable->likes()->syncWithoutDetaching($likee->id); // %NOTE!! %TODO: apply elsewhere instead of attach
        return response()->json([
            'likeable' => $likeable,
            'like_count' => $likeable->likes->count(),
        ]);
    }

    // %FIXME: not really REST-ful (ie likee param should be likeable, but not sure that makes sense in the UI)
    // ~ https://stackoverflow.com/questions/299628/is-an-entity-body-allowed-for-an-http-delete-request
    public function destroy(Request $request, User $likee)
    {
        $request->validate([
            'likeable_type' => 'required|string|alpha-dash|in:comments,mediafiles,posts,stories',
            'likeable_id' => 'required|numeric|min:1',
        ]);

        $alias = $request->likeable_type;
        $model = Relation::getMorphedModel($alias);
        $likeable = (new $model)->where('id', $request->likeable_id)->firstOrFail();

        if ($request->user()->cannot('like', $likeable)) {
            abort(403);
        }

        $likeable->likes()->detach($likee->id);

        return response()->json([
            'like_count' => $likeable->likes->count(),
        ]);
    }
}
