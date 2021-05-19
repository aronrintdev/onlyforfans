<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Shareable as ShareableModel;
use App\Models\Mediafile;
use App\Models\Vaultfolder;
use App\Models\Post;
use App\Models\Timeline;
use App\Models\User;
//use App\Notifications\ResourceShared; // %TODO
use App\Http\Resources\ShareableCollection;

class ShareablesController extends AppBaseController
{
    public function index(Request $request)
    {
        $request->validate([
            // filters
            'access_level' => 'string|in: default, premium',
            'shareable_type' => 'string|in: timelines, posts, mediafiles',
            'sharee_id' => 'uuid|exists:users,id', // if admin only
            'is_approved' => 'boolean',
            'access_level' => 'string:in:default,premium',
        ]);
        $filters = $request->only([
            'access_level',
            'shareable_type',
            'sharee_id',
            'is_approved',
            'access_level',
        ]) ?? [];

        // Init query
        $query = ShareableModel::with(['sharee', 'shareable']);

        // Check permissions
        if ( !$request->user()->isAdmin() ) {

            // %TODO : filter for session user only!! (aslo for likes)

            // non-admin: only view own...
            //$query->where('user_id', $request->user()->id); 
            //$query->take(5);
            $query->whereHasMorph(
                'shareable',
                [Post::class, Timeline::class],
                //[Post::class, Timeline::class, Mediafile::class],
                function (Builder $q1, $type) use(&$request) {
                    switch ($type) {
                    case Post::class:
                    case Timeline::class:
                        $column = 'user_id';
                        break;
                    case Mediafile::class:
                        throw new Exception('Mediafile shareables.index TBD');
                        $column = 'user_id';
                        break;
                    default:
                        throw new Exception('Invalid morphable type for Shareable: '.$type);
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

        //$query->sort( $sortBy, ($sortDir==='asc' ?? 'desc') );

        $data = $query->paginate( $request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)) );
        return new ShareableCollection($data);
    }


    // list of users/timelines following session user
    public function indexFollowers(Request $request)
    {
        $request->validate([
            // filters
            'accessLevel' => 'string|in:default,premium',
            //'onlineStatus' => 'string|in: default, premium',
            //'sharee_id' => 'uuid|exists:users,id', // if admin only
        ]);

        $sessionUser = $request->user();
        $sessionTimeline = $sessionUser->timeline;

        $query = ShareableModel::with(['sharee', 'shareable']); // init
        //$query->whereHasMorph( 'shareable', [Timeline::class], function (Builder $q1, $type) use(&$request) {
        //    $q1->where('user_id', $request->user()->id);
        //});
        $query->where('shareable_type', 'timelines');
        $query->where('shareable_id', $sessionTimeline->id);

        // Apply any filters
        if ( $request->has('accessLevel') ) {
            $query->where('access_level', $request->accessLevel);
        }
        if ( $request->has('onlineStatus') ) {
            //$query->where('access_level', $request->onlineStatus);
        }

        // Order By
        $sortDir = $request->sortDir==='asc' ? 'asc' : 'desc';
        switch ($request->sortBy) {
        case 'start_date':
            $query->orderBy( 'created_at', $sortDir );  // %FIXME: what if upgrade from free to subscribe?
            break;
        case 'activity':
            // %TODO
            break;
        case 'name':
            // %FIXME join will be faster see above link
            $query->orderBy( User::select('username')->whereColumn('users.id', 'shareables.sharee_id'), $sortDir ); 
            break;
        }

        $data = $query->paginate( $request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)) );
        return new ShareableCollection($data);
    }

    // list of users/timelines followed by session user
    // https://reinink.ca/articles/ordering-database-queries-by-relationship-columns-in-laravel
    public function indexFollowing(Request $request)
    {
        $request->validate([
            // filters
            'accessLevel' => 'string|in:default,premium',
            //'onlineStatus' => 'string|in:tbd',
            //'sharee_id' => 'uuid|exists:users,id', // if admin only
        ]);
        $sessionUser = $request->user();
        $sessionTimeline = $sessionUser->timeline;

        $query = ShareableModel::with([ 'sharee', 'shareable', 'shareable.avatar', 'shareable.cover' ]); // init
        $query->where('shareable_type', 'timelines');
        $query->where('sharee_id', $sessionUser->id);

        // Apply filters
        if ( $request->has('accessLevel') ) {
            $query->where('access_level', $request->accessLevel);
        }
        if ( $request->has('onlineStatus') ) {
            //$query->where('access_level', $request->onlineStatus);
        }

        // Order By
        $sortDir = $request->sortDir==='asc' ? 'asc' : 'desc';
        switch ($request->sortBy) {
        case 'start_date':
            $query->orderBy( 'created_at', $sortDir );  // %FIXME: what if upgrade from free to subscribe?
            break;
        case 'activity':
            // %TODO
            break;
        case 'name':
            // %FIXME join will be faster see above link
            $query->orderBy( Timeline::select('slug')->whereColumn('timelines.id', 'shareables.shareable_id'), $sortDir ); 
            break;
        }

        //return new ShareableCollection($query->get()); // %DEBUG
        $data = $query->paginate( $request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)) );
        return new ShareableCollection($data);
    }


    /*
    public function index(Request $request)
    {
        $sessionUser = Auth::user();

        $mediafiles = $sessionUser->sharedmediafiles->map( function($mf) {
            $mf->owner = $mf->getOwner()->first()->only('username', 'name', 'avatar');
            return $mf;
        });

        return response()->json([
            'shareables' => [
                'mediafiles' => $mediafiles,
                'vaultfolders' => $sessionUser->sharedvaultfolders,
            ],
        ]);
    }
     */

}
