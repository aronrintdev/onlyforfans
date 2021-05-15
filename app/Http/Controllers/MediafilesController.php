<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
//use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\MediafileCollection;
use App\Http\Resources\Mediafile as MediafileResource;
use App\Models\User;
use App\Models\Post;
use App\Models\Story;
use App\Models\Mediafile;
use App\Models\Diskmediafile;
use App\Enums\MediafileTypeEnum;

class MediafilesController extends AppBaseController
{

    public function index(Request $request)
    {
        $request->validate([
            //'post_id' => 'uuid|exists:posts,id', // if admin or post-owner only (per-post comments by fan use posts controller)
            'user_id' => 'uuid|exists:users,id', // if admin only
            'mftype' => 'in:'.MediafileTypeEnum::getKeysCsv(), // %TODO : apply elsewhere
        ]);

        $filters = $request->only(['user_id', 'mftype' ]) ?? [];

        // Init query
        $query = Mediafile::query();

        // Check permissions
        if ( !$request->user()->isAdmin() ) {

            // non-admin: can only view own
            $query->whereHasMorph(
                'resource',
                [Post::class, Story::class, User::class],
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
            }
        }

        $data = $query->paginate( $request->input('take', env('MAX_MEDIAFILES_PER_REQUEST', 10)) );
        return new MediafileCollection($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'mediafile' => 'required',
            'mftype' => 'required|in:'.MediafileTypeEnum::getKeysCsv(),
            'resource_type' => 'nullable|alpha-dash|in:comments,posts,stories,vaultfolders',
            'resource_id' => 'required_with:resource_type|uuid',
        ]);

        $file = $request->file('mediafile');

        // If tied to a resource, check RBAC permission/policy for updating that resource...
        if ( $request->has('resource_type') ) {
            $alias = $request->resource_type;
            $model = Relation::getMorphedModel($alias);
            $resource = (new $model)->where('id', $request->resource_id)->first();
            if ( empty($resource) ) {
                abort(404);
            }
            if ( $request->user()->cannot('update', $resource) ) {
                abort(403);
            }
        }

        try {
            /*
            $mediafile = DB::transaction(function () use(&$file, &$request) {
                $owner = $request->user(); // the orig. content OWNER
                $subFolder = $owner->id;
                //$s3Filename = $file->store('./'.$subFolder, 's3');
                $s3Filename = $file->store($subFolder, 's3');
                $mfname = $mfname ?? $file->getClientOriginalName();
                $diskmediafile = Diskmediafile::create([
                    'filename' => $s3Filename,
                    'mimetype' => $file->getMimeType(),
                    'owner_id' => $owner->id,
                    'orig_filename' => $file->getClientOriginalName(),
                    'orig_ext' => $file->getClientOriginalExtension(),
                    'cattrs' => $request->input('cattrs') ?? null,
                    'meta' => $request->input('meta') ?? null,
                ]);
                $mediafile = Mediafile::create([
                    'diskmediafile_id' => $diskmediafile->id,
                    'resource_id' => $request->resource_id,
                    'resource_type' => $request->resource_type,
                    'mfname' => $mfname,
                    'mftype' => $request->mftype,
                    'cattrs' => $request->input('cattrs') ?? null,
                    'meta' => $request->input('meta') ?? null,
                ]);
                return $mediafile;
            });
            */

            $owner = $request->user(); // the orig. content OWNER
            $subFolder = $owner->id;
            $s3path = $file->store($subFolder, 's3');
            $mfname = $mfname ?? $file->getClientOriginalName();

            $mediafile = Diskmediafile::doCreate([
                $s3Path,                            // $s3Filepath
                $mfname,                            // $mfname
                $request->mftype,                   // $mftype
                $owner,                             // $owner
                $request->resource_id,              // $resourceID
                $request->resource_type,            // $resourceType
                $mimetype,                          // $mimetype
                $file->getClientOriginalName(),     // $origFilename
                $file->getClientOriginalExtension() // $origExt
            ]);
        } catch (\Exception $e) {
            throw $e; // %FIXME: report error to user via browser message
        }

        return response()->json([ 
            'mediafile' => $mediafile,
        ], 201);
    }

    public function show(Request $request, Mediafile $mediafile)
    {
        $this->authorize('view', $mediafile);
        /*
        if ( $request->user()->cannot('view', $mediafile) ) {
            abort(403);
        }
         */

        // Create sharable link
        //   ~ https://laravel.com/docs/5.5/filesystem#retrieving-files
        if (env('APP_ENV') === 'testing') {
            // %NOTE: workaround for S3 in testing env
            return $mediafile->filename;
        } else {
            $url = Storage::disk('s3')->temporaryUrl(
                $mediafile->filename,
                now()->addMinutes(5) // %FIXME: hardcoded
            );
        }
        return new MediafileResource($mediafile);
    }

    public function update(Request $request, $pkid)
    {
        $this->validate($request, [
            'mfname' => 'string|alpha_dash',
        ]);

        try {

            $obj = mediafile::find($pkid);
            if ( empty($obj) ) {
                throw new ModelNotFoundException('Could not find Mediafile with pkid '.$pkid);
            }

            $obj = DB::transaction(function () use ($request, $obj) {
                $obj->fill($request->all());
                $obj->save();
                return $obj;
            });

        } catch (\Exception $e) {
            throw $e;
            //$messages = [$e->getMessage()];
            //$response = ['is_ok' => 0,'messages' => $messages];
        }

        if ( \Request::ajax() ) {
            return \Response::json([ 'obj' => $obj ]);
        } else {
            return \Redirect::route('admin.users.show', [$obj->slug]);
        }

    }


    /*
    public function destroy($pkid)
    {
        $sessionUser = Auth::user();

        $obj = Mediafile::find($pkid);
        if ( empty($obj) ) {
            throw new ModelNotFoundException('Could not find mediafile with pkid '.$pkid);
        }
        $msg = 'There was a problem...'; // default

        if ( !$obj->isDeletable() ) {
            $msg = 'Delete not permitted on media file with guid: '.$obj->renderField('guid');
        } else {
            $obj->deleteMF();
            $msg = 'Media file with guid '.$obj->renderField('guid').' successfully deleted';
        }
        return back()->with('message',$msg);
    }
     */

}

// $path = "public/directory_pics/MAaKSCm96gaep1cMulfasWBWupVs33Z6GZ5RcfU4.png"
// $fullPath = "/Users/petergorgone/workspace/cdn/jmbm/intranet-v4/public/directory_pics/MAaKSCm96gaep1cMulfasWBWupVs33Z6GZ5RcfU4.png"
