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
        if ( !$this->isAllowed($request->user()) ) {
            abort(403);
        }

        $request->validate([
            //'post_id' => 'uuid|exists:posts,id', // if admin or post-owner only (per-post comments by fan use posts controller)
            //'user_id' => 'uuid|exists:users,id', // if admin only
            //'mftype' => 'in:'.MediafileTypeEnum::getKeysCsv(), // %TODO : apply elsewhere
            //'mediafile_ids' => 'array',
            //'mediafile_ids.*' => 'uuid|exists:mediafiles,id',
        ]);

        $filters = $request->only([ 'owner_id', 'mimetype', 'orig_ext', 'orig_filename', 'basename', ]) ?? [];

        // Init query
        $query = Diskmediafile::query();

        // Apply any filters
        foreach ($filters as $key => $f) {
            switch ($key) {
                case 'owner_id':
                case 'mimetype':
                case 'orig_ext':
                case 'orig_filename':
                case 'basename':
                    $query->where($key, $f);
                    break;
            }
        }

        $data = $query->paginate( $request->input('take', env('MAX_MEDIAFILES_PER_REQUEST', 10)) );
        return new DiskmediafileCollection($data);
    }


    public function show(Request $request, Diskmediafile $diskmediafile)
    {
        if ( !$this->isAllowed($request->user()) ) {
            abort(403);
        }
    }


    // deletes a single mediafile (reference)
    public function destroy(Request $request, Diskmediafile $diskmediafile)
    {
        if ( !$this->isAllowed($request->user()) ) {
            abort(403);
        }
        //$this->authorize('delete', $mediafile);
        //$mediafile->diskmediafile->deleteReference($mediafile->id, false);
        //return response()->json([], 204);
    }

    private function isAllowed($user) {
        $roles = $user->roles()->pluck('name');
        return $roles->contains('super-admin') || $roles->contains('admin');
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
