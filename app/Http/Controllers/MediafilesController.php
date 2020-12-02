<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;
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
        try {
            /*
            $request->validate([
                'mediafile' => 'required',
                'resource_id' => 'required',
                'resource_type' => 'required',
                'mftype' => 'required',
            ]);
             */
        } catch (\Exception $e) {
            if ( $request->ajax() ) {
                return \Response::json(['message'=> $e->getMessage()],422);
            } else {
                throw $e; // %FIXME: report error to user via browser message
            }
        }

        $file = $request->file('mediafile');
        //dd($file); // lluminate\Http\Testing\File

        try {

            DB::beginTransaction();

            switch ($request->mftype) {
                default:
                    //$newFilename = Storage::putFile('s3', new File($file->), 'public');
                    //$newFilename = Storage::disk('s3')->putFile('./', $file, 'public');
                    //$newFilename = Storage::putFile('s3', new File('/fans/stories'), 'public');
                    //$newFilename = $file->store('/fans/stories'.
                    $mediafile = Mediafile::create([
                        'resource_id' => $request->resource_id,
                        'resource_type' => $request->resource_type,
                        //'filename' => $newFilename,
                        'mftype' => $request->mftype,
                        'meta' => $request->input('meta') ?? null,
                        'mimetype' => $file->getMimeType(),
                        'orig_filename' => $file->getClientOriginalName(),
                        'orig_ext' => $file->getClientOriginalExtension(),
                    ]);

                    $newFilename = Storage::disk('s3')->put('fans-platform/'.$mediafile->guid, file_get_contents($file));
        
                    //$newFilename = $mediafile->guid.'.'.$mediafile->ext;
                    //$storeTo = STORAGE_SUBPATH_GENERAL;
                    //$path = $file->storeAs($storeTo, $newFilename);
                    //Storage::disk('s3')->put('avatars/1', $fileContents);
                    //$fullpath = Storage::path($path);
                    break;
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            throw $e; // %FIXME: report error to user via browser message
        }
        if (\Request::ajax()) {
            return response()->json([ 'obj' => $mediafile ]);
        } else {
            return back()->withInput();
        }

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
