<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
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
use App\Models\Vaultfolder;
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
            'mediafile_ids' => 'array',
            'mediafile_ids.*' => 'uuid|exists:mediafiles,id',
        ]);

        $filters = $request->only(['user_id', 'mftype', 'mediafile_ids' ]) ?? [];

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

    public function store(Request $request)
    {
        $this->validate($request, [
            'mediafile' => 'required_without:mediafile_id', // upload content/diskmediafile to s3
            'mediafile_id' => 'uuid|exists:mediafiles,id', // create a mediafile 'reference' to existing diskmediafile
            'mftype' => 'required|in:'.MediafileTypeEnum::getKeysCsv(),
            'resource_type' => 'nullable|alpha-dash|in:comments,posts,stories,vaultfolders,chatmessages',
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
                // can not update associated resource (eg, post, message, etc)
                abort(403);
            }
        }

        try {

            if ( $request->has('mediafile_id') ) {
                // Create a reference to an existing [diskmediafile] record, via the mediafile_id in request param
                $mediafile = Mediafile::find($request->mediafile_id);

                $this->authorize('update', $mediafile);

                $mfname = $request->input('mfname', $mediafile->mfname);
                //$diskmediafile = Diskmediafile::find($request->diskmediafile_id);
                $mediafile->diskmediafile->createReference(
                    $request->resource_type,   // string   $resourceType
                    $request->resource_id,     // int      $resourceID
                    $mfname,                   // string   $mfname
                    $request->mftype           // string   $mftype
                );
            } else {
                // Upload contents of file to S3 & create a new [diskmediafiles] record
                $owner = $request->user(); // the orig. content OWNER
                $subFolder = $owner->id;
                $s3Path = $file->store($subFolder, 's3');
                $mfname = $request->input('mfname') ?? $file->getClientOriginalName();
    
                $mediafile = Diskmediafile::doCreate([
                    'owner_id'      => $owner->id,
                    'filepath'      => $s3Path,
                    'mimetype'      => $file->getClientMimeType(),
                    'orig_filename' => $file->getClientOriginalName(),
                    'orig_ext'      => $file->getClientOriginalExtension(),
                    'mfname'        => $mfname,
                    'mftype'        => $request->mftype,
                    'resource_id'   => $request->resource_id,
                    'resource_type' => $request->resource_type,
                ]);
            }
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

    // deletes a single mediafile (reference)
    public function destroy(Request $request, Mediafile $mediafile)
    {
        $this->authorize('delete', $mediafile);
        $mediafile->diskmediafile->deleteReference($mediafile->id, false);
        return response()->json([], 204);
    }

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

    // Get stats on related media
    public function diskStats(Mediafile $mediafile) 
    {
        $this->authorize('update', $mediafile);

        $dmf = Diskmediafile::find($mediafile->diskmediafile_id);

        if ($dmf) {
            $stats = [
                'disk' => [
                    'owner' => $dmf->owner->name,
                    'mimetype' => $dmf->mimetype,
                    'ext' => $dmf->orig_ext,
                    'filename' => $dmf->orig_filename,
                    'size' => $dmf->orig_size,
                    'created_at' => $dmf->created_at,
                ],
                'refs' => [],
            ];
            $dmf->mediafiles->each( function($mf) use(&$stats) {
                $stats['refs'][] = [
                    'id' => $mf->id,
                    'name' => $mf->mfname,
                    'mftype' => $mf->mftype,
                    'num_sharees' => $mf->sharees->count(),
                    'resource_type' => $mf->resource_type,
                    'resource_name' => $mf->resource->slug,
                    'num_resource_likes' => $mf->resource->likes ? $mf->resource->likes->count() : '-',
                ];
            });
        }

        return response()->json([
            'stats' => $stats ?? [],
        ]);
    }

}

// $path = "public/directory_pics/MAaKSCm96gaep1cMulfasWBWupVs33Z6GZ5RcfU4.png"
// $fullPath = "/Users/petergorgone/workspace/cdn/jmbm/intranet-v4/public/directory_pics/MAaKSCm96gaep1cMulfasWBWupVs33Z6GZ5RcfU4.png"
