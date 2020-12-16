<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Setting;
use App\Story;
use App\Mediafile;
use App\Enums\MediafileTypeEnum;

class StoriesController extends AppBaseController
{
    public function player(Request $request, $username)
    {
        $sessionUser = Auth::user();
        $stories = Story::where('timeline_id', $sessionUser->timeline->id)->get();
        $storiesA = $stories->map( function($item, $iter) {
            $a = $item->toArray();
            if ( count($item->mediafiles) ) {
                $fn = $item->mediafiles[0]->filename;
                $a['mf_filename'] = $fn;
                $a['mf_url'] = Storage::disk('s3')->url($fn);
            }
            return $a;
        });

        $this->_php2jsVars['session'] = [
            'username' => $sessionUser->username,
        ];
        \View::share('g_php2jsVars',$this->_php2jsVars);

        return view('stories.player', [
            'sessionUser' => $sessionUser,
            'stories' => $storiesA,
            'timeline' => $sessionUser->timeline,
        ]);
    }

    public function index(Request $request, $username)
    {
        $sessionUser = Auth::user();
        $stories = Story::where('timeline_id', $sessionUser->timeline->id)->get();

        //$html = view('stories._index', compact('sessionUser', 'stories'))->render();
        $html = view('stories._index', [
            'sessionUser' => $sessionUser,
            'stories' => $stories,
        ])->render();

        return response()->json([
            'html' => $html,
        ]);
    }

    public function create(Request $request, $username)
    {
        $sessionUser = Auth::user();
        /*
        dd( 
            $sessionUser->avatar,
            $sessionUser->timeline->toArray()
        );
         */
        $stories = $sessionUser->timeline->stories;
        $storiesA = $stories->map( function($item, $iter) {
            $a = $item->toArray();
            if ( count($item->mediafiles) ) {
                $fn = $item->mediafiles[0]->filename;
                $a['mf_filename'] = $fn;
                $a['mf_url'] = Storage::disk('s3')->url($fn);
            }
            return $a;
        });
        return view('stories.create', [
            'session_user' => $sessionUser,
            'timeline' => $sessionUser->timeline,
            'stories' => $storiesA,
            'dtoUser' => [
                'avatar_url' => $sessionUser->avatar,
                'fullname' => $sessionUser->timeline->name,
                'username' => $sessionUser->timeline->username,
            ],
        ]);
    }

    public function store(Request $request, $username)
    {
        $request['attrs'] = json_decode($request['attrs'], true); // decode 'complex' data
        
        $sessionUser = Auth::user();
        $this->validate($request, [
            'attrs' => 'required',
            'attrs.stype' => 'required',
            'mediafile' => 'required_if:attrs.stype,image|file',
        ]);


        try {
            $obj = DB::transaction(function () use(&$sessionUser, &$request) {

                $story = Story::create([
                    'timeline_id' => $sessionUser->timeline_id,
                    'content' => $request->attrs['content'] ?? null,
                    'cattrs' => [
                        'background-color' => array_key_exists('bgcolor', $request->attrs) ? $request->attrs['bgcolor'] : '#fff',
                    ],
                    'stype' => $request->attrs['stype'],
                ]);

                if ( $request->attrs['stype'] === 'image' ) {
                    $file = $request->file('mediafile');
                    $subFolder = 'stories';
                    $newFilename = $file->store('fans-platform/'.$subFolder, 's3'); // %FIXME: hardcoded
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
                }
                return $story;
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
            return response()->json([ 'obj' => $obj ]);
        } else {
            return back()->withInput();
        }
    }

}
