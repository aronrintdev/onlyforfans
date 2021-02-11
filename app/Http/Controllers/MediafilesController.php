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
use App\Models\User;
use App\Models\MediaFile;
use App\Enums\MediaFileTypeEnum;

class MediaFilesController extends AppBaseController
{

    public function store(Request $request)
    {
        $this->validate($request, [
            'mediaFile' => 'required',
            'type' => 'required|in:avatar,cover,post,story,vault',
            'resource_type' => 'nullable|alpha-dash|in:comments,posts,stories,vault_folders',
            'resource_id' => 'required_with:resource_type|numeric|min:1',
        ]);

        $file = $request->file('mediaFile');

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
            $mediaFile = DB::transaction(function () use(&$file, &$request) {
                switch ($request->type) {
                    case 'vault':
                        $subFolder = 'vaultFolders';
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
                $name = $name ?? $file->getClientOriginalName();
                $mediaFile = MediaFile::create([
                    'resource_id' => $request->resource_id,
                    'resource_type' => $request->resource_type,
                    'filename' => $newFilename,
                    'name' => $name,
                    'type' => $request->type,
                    'meta' => $request->input('meta') ?? null,
                    'mimetype' => $file->getMimeType(),
                    'orig_filename' => $file->getClientOriginalName(),
                    'orig_ext' => $file->getClientOriginalExtension(),
                ]);
                return $mediaFile;
            });
        } catch (\Exception $e) {
            throw $e; // %FIXME: report error to user via browser message
        }

        return response()->json([ 
            'mediaFile' => $mediaFile,
        ], 201);
    }

    public function show(Request $request, MediaFile $mediaFile)
    {
        $this->authorize('view', $mediaFile);
        /*
        if ( $request->user()->cannot('view', $mediaFile) ) {
            abort(403);
        }
         */

        // Create sharable link
        //   ~ https://laravel.com/docs/5.5/filesystem#retrieving-files
        if (env('APP_ENV') === 'testing') {
            // %NOTE: workaround for S3 in testing env
            return $mediaFile->filename;
        } else {
            $url = Storage::disk('s3')->temporaryUrl(
                $mediaFile->filename,
                now()->addMinutes(5) // %FIXME: hardcoded
            );
        }
        return response()->json([
            'mediaFile' => $mediaFile,
            'url' => $url,
        ]);
    }

    public function update(Request $request, $pkid)
    {
        $this->validate($request, mediaFile::$vrules);

        try {

            $obj = mediaFile::find($pkid);
            if ( empty($obj) ) {
                throw new ModelNotFoundException('Could not find mediaFile with pkid '.$pkid);
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

        $obj = mediaFile::find($pkid);
        if ( empty($obj) ) {
            throw new ModelNotFoundException('Could not find mediaFile with pkid '.$pkid);
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
