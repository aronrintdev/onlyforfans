<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Setting;
use App\Story;
use App\Mediafile;
use App\Enums\MediafileTypeEnum;

class StoriesController extends AppBaseController
{
    public function index(Request $request, $username)
    {
        $sessionUser = Auth::user();

        $stories = Story::where('timeline_id', $sessionUser->timeline->id)->get();

        //$html = view('stories._index', compact('sessionUser', 'stories'))->render();
        $html = view('stories._index', [
            'sessionUser' => $sessionUser,
            'stories' => $stories,
        ])->render();
        /*
         */

        return response()->json([
            'html' => $html,
        ]);
    }

    public function create(Request $request, $username)
    {
        $sessionUser = Auth::user();
        return view('stories.create', [
            'session_user' => $sessionUser,
            'timeline' => $sessionUser->timeline,
        ]);
    }

    public function store(Request $request, $username)
    {
        $request['attrs'] = json_decode($request['attrs'], true); // decode 'complex' data
        
        $sessionUser = Auth::user();
        $this->validate($request, [
            'mediafile' => 'required|file',
            'attrs' => 'required',
            'attrs.stype' => 'required',
        ]);

        $file = $request->file('mediafile');

        try {
            $mediafile = DB::transaction(function () use(&$sessionUser, &$request, &$file) {

                $story = Story::create([
                    'timeline_id' => $sessionUser->timeline_id,
                    'content' => $request->attrs['content'] ?? null,
                    'cattrs' => [
                        'background-color' => $request->bgcolor ?? '#fff',
                    ],
                    'stype' => $request->attrs['stype'],
                ]);

                $newFilename = $file->store('fans-platform/stories', 's3'); // %FIXME: hardcoded
                $mediafile = Mediafile::create([
                    'resource_id' => $story->id,
                    'resource_type' => 'stories',
                    'filename' => $newFilename,
                    'mftype' => MediafileTypeEnum::STORY,
                    'meta' => $request->input('attrs.foo') ?? null,
                    'cattrs' => $request->input('attrs.bar') ?? null,
                    'mimetype' => $file->getMimeType(),
                    'orig_filename' => $file->getClientOriginalName(),
                    'orig_ext' => $file->getClientOriginalExtension(),
                ]);
                return $mediafile;
            });
        } catch (Exception $e) {
            // %TODO: delete file on s3 if it got uploaded
            Log::error(json_encode([
                'msg' => $e->getMessage(),
                'debug' => ['request'=>$request->all()],
            ], JSON_PRETTY_PRINT ));
            throw $e; // %FIXME: report error to user via browser message
        }

        if ( $request->ajax() ) {
            return response()->json([ 'obj' => $mediafile ]);
        } else {
            return back()->withInput();
        }
    }

}
