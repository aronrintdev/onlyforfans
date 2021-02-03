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
//use App\Enums\StoryTypeEnum; // generalize?

class StoriesController extends AppBaseController
{

    public function index(Request $request)
    {
        $sessionUser = Auth::user();
        $sessionTimeline = $sessionUser->timeline;

        $filters = $request->input('filters', []);

        $query = Story::query();
        $query->with('mediafiles');

        if ( !$sessionUser->isAdmin() ) {
            $query->where('timeline_id', $sessionTimeline->id);
        }

        $stories = $query->get();

        return response()->json([
            'stories' => $stories,
        ]);
    }

    public function store(Request $request)
    {
        $request['attrs'] = json_decode($request['attrs'], true); // decode 'complex' data
        
        $sessionUser = Auth::user();
        $this->validate($request, [
            'attrs' => 'required',
            'attrs.stype' => 'required|in:text,photo',
            'mediafile' => 'required_if:attrs.stype,photo|file',
        ]);

        try {
            $story = DB::transaction(function () use(&$sessionUser, &$request) {

                $story = Story::create([
                    'timeline_id' => $sessionUser->timeline_id,
                    'content' => $request->attrs['content'] ?? null,
                    'cattrs' => [
                        'background-color' => array_key_exists('bgcolor', $request->attrs) ? $request->attrs['bgcolor'] : '#fff',
                    ],
                    'stype' => $request->attrs['stype'],
                ]);

                if ( $request->attrs['stype'] === 'photo' ) {
                    $file = $request->file('mediafile');
                    $subFolder = 'stories';
                    $newFilename = $file->store('./'.$subFolder, 's3'); // %FIXME: hardcoded
                    $mediafile = Mediafile::create([
                        'resource_id' => $story->id,
                        'resource_type' => 'stories',
                        'filename' => $newFilename,
                        'mfname' => $mfname ?? $file->getClientOriginalName(),
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
            abort(400);
        }

        return response()->json([ 'story' => $story ]);
    }

    public function player(Request $request)
    {
        $sessionUser = Auth::user();
        $stories = Story::where('timeline_id', $sessionUser->timeline->id)->get();
        $storiesA = $stories->map( function($item, $iter) {
            $a = $item->toArray();
            if ( count($item->mediafiles) ) {
                $fn = $item->mediafiles[0]->filename;
                $a['mf_filename'] = $fn;
                $a['mf_url'] = Storage::disk('s3')->url($fn); // %FIXME: use model attribute
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

    public function dashboard(Request $request)
    {
        $sessionUser = Auth::user();
        $stories = $sessionUser->timeline->stories;
        $storiesA = $stories->map( function($item, $iter) {
            $a = $item->toArray();
            if ( count($item->mediafiles) ) {
                $fn = $item->mediafiles[0]->filename;
                $a['mf_filename'] = $fn;
                $a['mf_url'] = Storage::disk('s3')->url($fn); // %FIXME: use model attribute
            }
            return $a;
        });
        return view('stories.create', [
            'session_user' => $sessionUser,
            'timeline' => $sessionUser->timeline,
            'stories' => $storiesA,
            'dtoUser' => [
                'avatar' => $sessionUser->avatar,
                'fullname' => $sessionUser->timeline->name,
                'username' => $sessionUser->timeline->username,
            ],
        ]);
    }

}

