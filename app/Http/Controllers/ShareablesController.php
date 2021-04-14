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
        ]);

        // Init query
        $query = ShareableModel::with(['sharee', 'shareable']);

        // Check permissions
        if ( !$request->user()->isAdmin() ) {

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
        if ( $request->has('shareable_type') ) {
            $query->where('shareable_type', $request->shareable_type);
        }
        if ( $request->has('access_level') ) {
            $query->where('access_level', $request->access_level);
        }

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
