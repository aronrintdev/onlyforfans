<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\DiskmediafileCollection;
use App\Http\Resources\Diskmediafile as DiskmediafileResource;
use App\Models\Mediafile;
use App\Models\Diskmediafile;
use App\Enums\MediafileTypeEnum;

class DiskmediafilesController extends AppBaseController
{

    public function index(Request $request)
    {
        $request->validate([
            //'post_id' => 'uuid|exists:posts,id', // if admin or post-owner only (per-post comments by fan use posts controller)
            //'user_id' => 'uuid|exists:users,id', // if admin only
            //'mftype' => 'in:'.MediafileTypeEnum::getKeysCsv(), // %TODO : apply elsewhere
            //'mediafile_ids' => 'array',
            //'mediafile_ids.*' => 'uuid|exists:mediafiles,id',
        ]);

        //$filters = $request->only(['user_id', 'mftype', 'mediafile_ids' ]) ?? [];

        // Init query
        $query = Mediafile::query();

        // Check permissions
        if ( !$request->user()->isAdmin() ) {

            // non-admin: can only view own
            $query->whereHasMorph(
                'resource',
                [Post::class, Story::class, User::class, Vaultfolder::class],
                function (Builder $q1, $type) use(&$request) {
                    switch ($type) {
                    case Post::class:
                        $q1->where('user_id', $request->user()->id);
                        break;
                    case Story::class:
                        $column = 'timelines.user_id';
                        $q1->whereHas('timeline', function($q2) use(&$request) {
                            $q2->where('user_id', $request->user()->id);
                        });
                        break;
                    case User::class:
                        $q1->where('id', $request->user()->id);
                        break;
                    case Vaultfolder::class:
                        $q1->whereHas('vault', function($q2) use(&$request) {
                            $q2->where('user_id', $request->user()->id);
                        });
                        break;
                    default:
                        throw new Exception('Invalid morphable type for resource: '.$type);
                    }
                }
            );
            unset($filters['user_id']);
        }

        // Apply any filters
        foreach ($filters as $key => $f) {
            switch ($key) {
                case 'mftype':
                case 'user_id':
                //case 'post_id':
                    $query->where($key, $f);
                    break;
                case 'mediafile_ids':
                    $query->whereIn('id', $f);
                    break;
            }
        }

        $data = $query->paginate( $request->input('take', env('MAX_MEDIAFILES_PER_REQUEST', 10)) );
        return new MediafileCollection($data);
    }


    public function show(Request $request, Diskmediafile $diskmediafile)
    {
    }


    // deletes a single mediafile (reference)
    public function destroy(Request $request, Diskmediafile $diskmediafile)
    {
        //$this->authorize('delete', $mediafile);
        //$mediafile->diskmediafile->deleteReference($mediafile->id, false);
        //return response()->json([], 204);
    }

    /*
    public function batchDestroy(Request $request)
    {
        $this->validate($request, [
            'mediafile_ids' => 'required|array',
            'mediafile_ids.*' => 'uuid|exists:mediafiles,id',
        ]);
        $mediafiles = Mediafile::whereIn('id', $request->mediafile_ids);
        $mediafiles->each( function($mf) {
            try {
                $this->authorize('delete', $mf);
                $mf->diskmediafile->deleteReference($mf->id, false);
            } catch (Exception $e) {
                Log::warning('mediafiles.batchDestroy - Could not delete mediafile with pkid = '.$mf->id.' : '.$e->getMessage());
                abort(403);
            }
        });
        return response()->json([], 204);
    }
    */

}
