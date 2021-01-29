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
        $sessionUser = Auth::user();
        $likeables = collect(); // %TODO
        return response()->json([
            'likeables' => $likeables,
        ]);
    }

    // %TODO: notify user
    // to use an arg like Likeable $likeable, replacing getMorphedModel below, see
    //  ~ https://www.reddit.com/r/laravel/comments/ai0x6w/polymorphic_route_model_binding/eek37vs
    //  ~ https://laravel.com/docs/8.x/routing#route-model-binding
    public function update(Request $request, User $likee)
    {
        $request->validate([
            'likeable_type' => 'required|string|alpha-dash|in:posts,comments,mediafiles',
            'likeable_id' => 'required|numeric|min:1',
        ]);

        $alias = $request->likeable_type;
        $model = Relation::getMorphedModel($alias);
        $likeable = (new $model)->where('id', $request->likeable_id)->firstOrFail();

        $likeable->likes()->attach($likee->id);
        /*
        $now = \Carbon\Carbon::now();
        $like = DB::table('likeables')->insertOrIgnore([
            'likee_id' => $likee->id,
            'likeable_type' => $request->likeable_type,
            'likeable_id' => $request->likeable_id,
            'created_at' => $now,
            'update_at' => $now,
        ]);
         */
        return response()->json([
            'likeable' => $likeable,
        ]);
    }

    public function destroy(Request $request, User $likee)
    {
        $like = DB::table('likeables')
            ->where('likee_id', $likee->id)
            ->where('likeable_type', $request->likeable_type)
            ->where('likeable_id', $request->likeable_id)
            ->first();

        if (!$like) {
            abort(404);
        }

        $like->delete();
        return response()->json([]);
    }
}
