<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
//use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\User;
use App\Mediafile;
use App\Enums\MediafileTypeEnum;

class MediafilesController extends AppBaseController
{

    public function store(Request $request)
    {
        $this->validate($request, [
            'mediafile' => 'required',
            'mftype' => 'required|in:avatar,cover,post,story,vault',
            'resource_type' => 'nullable|alpha-dash|in:comments,posts,stories,vaultfolders',
            'resource_id' => 'required_with:resource_type|numeric|min:1',
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
            $mediafile = DB::transaction(function () use(&$file, &$request) {
                switch ($request->mftype) {
                    case 'vault':
                        $subFolder = 'vaultfolders';
                        break;
                    case 'story':
                        $subFolder = 'stories';
                        break;
                    case 'post':
                        $subFolder = 'posts';
                        break;
                    default:
                        $subFolder = 'default';
                }
                $newFilename = $file->store('./'.$subFolder, 's3');
                $mfname = $mfname ?? $file->getClientOriginalName();
                $mediafile = Mediafile::create([
                    'resource_id' => $request->resource_id,
                    'resource_type' => $request->resource_type,
                    'filename' => $newFilename,
                    'mfname' => $mfname,
                    'mftype' => $request->mftype,
                    'meta' => $request->input('meta') ?? null,
                    'mimetype' => $file->getMimeType(),
                    'orig_filename' => $file->getClientOriginalName(),
                    'orig_ext' => $file->getClientOriginalExtension(),
                ]);
                return $mediafile;
            });
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
        //dd('controller.0');
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
        return response()->json([ 
            'mediafile' => $mediafile,
            'url' => $url,
        ]);
    }

    public function update(Request $request, $pkid)
    {
        $this->validate($request, Mediafile::$vrules);

        try {

            $obj = Mediafile::find($pkid);
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

    } // update()

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
// $fullpath = "/Users/petergorgone/workspace/cdn/jmbm/intranet-v4/public/directory_pics/MAaKSCm96gaep1cMulfasWBWupVs33Z6GZ5RcfU4.png"
