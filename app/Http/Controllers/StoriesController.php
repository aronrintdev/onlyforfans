<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\MediaFile;
use App\Setting;
use App\Story;
use App\Timeline;
use App\Enums\MediaFileTypeEnum;
//use App\Enums\StoryTypeEnum; // generalize?

class StoriesController extends AppBaseController
{

    // this is the general REST version. There should also be timelines/{timeline}/stories (?)
    public function index(Request $request)
    {
        $filters = [];
        if ( !$request->has('filters') || empty($request->filters) ) {
            $filters['following'] = true;
        }  else {
            $filters = $request->filters;
        }

        if ( !$request->user()->isAdmin() ) {
            do {
                if ( array_key_exists('following', $filters) ) {
                    break; // allowed
                }
                if ( array_key_exists('timeline_id', $filters) ) {
                    $timeline = Timeline::findOrFail($request->filters['timeline_id']);
                    if ( $request->user()->can('view', $timeline) ) { // should include followers & owner (!)
                        break; // allowed
                    }
                }
                abort(403); // none of the above match, therefore unauthorized
            } while(0);
        }

        $query = Story::query()->with('mediaFiles');

        foreach ( $request->input('filters', []) as $k => $v ) {
            switch ($k) {
            case 'following':
                $query->whereHas('timeline', function($q1) use(&$request) {
                    $q1->whereIn('id', $request->user()->followedTimelines);
                });
                break;
            default:
                $query->where($k, $v);
            }
        }
        $stories = $query->get();

        return response()->json([
            'stories' => $stories,
        ]);
    }

    public function store(Request $request)
    {
        $request['attrs'] = json_decode($request['attrs'], true); // decode 'complex' data

        $vrules = [
            'attrs' => 'required',
            'attrs.stype' => 'required|in:text,photo',
        ];
        if ( $request->has('mediaFile') ) {
            if ( $request->hasFile('mediaFile') ) {
                $vrules['mediaFile'] = 'required_if:attrs.stype,photo|file';
            } else {
                $vrules['mediaFile'] = 'required_if:attrs.stype,photo|integer|exists:mediaFiles,id'; // must be fk to [mediaFiles]
            }
        }
        
        $this->validate($request, $vrules);

        // policy check is redundant as a story is always created on session user's
        //   timeline, however in the future we may be more flexible, or support
        //   multiple timelines which will require request->timeline_id
        $timeline = Timeline::find($request->user()->timeline_id);
        $this->authorize('update', $timeline);

        try {
            $story = DB::transaction(function () use(&$request, &$timeline) {

                $story = Story::create([
                    'timeline_id' => $timeline->id,
                    'content' => $request->attrs['content'] ?? null,
                    'custom_attributes' => [
                        'background-color' => array_key_exists('bgcolor', $request->attrs) ? $request->attrs['bgcolor'] : '#fff',
                    ],
                    'type' => $request->attrs['type'],
                ]);

                if ( $request->attrs['stype'] === 'photo' ) {
                    if ( $request->hasFile('mediaFile') ) {
                        $file = $request->file('mediaFile');
                        $subFolder = 'stories';
                        $newFilename = $file->store('./'.$subFolder, 's3'); // %FIXME: hardcoded
                        $mediaFile = MediaFile::create([
                            'resource_id' => $story->id,
                            'resource_type' => 'stories',
                            'filename' => $newFilename,
                            'name' => $mediaFileName ?? $file->getClientOriginalName(),
                            'type' => MediaFileTypeEnum::STORY,
                            'metadata' => $request->input('attrs.foo') ?? null,
                            'custom_attributes' => $request->input('attrs.bar') ?? null,
                            'mimetype' => $file->getMimeType(),
                            'orig_filename' => $file->getClientOriginalName(),
                            'orig_ext' => $file->getClientOriginalExtension(),
                        ]);
                    } else {
                        $src = MediaFile::find($request->mediaFile);
                        $cloned = $src->doClone('stories', $story->id);
                        $story->mediaFiles()->save($cloned);
                    }
                }
                return $story;
            });
        } catch (Exception $e) {
            abort(400);
        }

        return response()->json([
            'story' => $story,
        ], 201);
    }

    public function show(Request $request, Story $story)
    {
        $this->authorize('view', $story);
        return response()->json([
            'story' => $story,
        ]);
    }

    public function destroy(Request $request, Story $story)
    {
        $this->authorize('delete', $story);

        // %TODO: use DB transaction
        $story->mediaFiles->each( function($mf) {
            Storage::disk('s3')->delete($mf->filename); // Remove from S3
            $mf->delete();
        });
        $story->delete();

        return response()->json([]);
    }

    // --

    public function player(Request $request)
    {
        $stories = Story::where('timeline_id', $request->user()->timeline->id)->get();
        $storiesA = $stories->map( function($item, $iter) {
            $a = $item->toArray();
            if ( count($item->mediaFiles) ) {
                $fn = $item->mediaFiles[0]->filename;
                $a['mf_filename'] = $fn;
                $a['mf_url'] = Storage::disk('s3')->url($fn); // %FIXME: use model attribute
            }
            return $a;
        });

        $this->_php2jsVars['session'] = [
            'username' => $request->user()->username,
        ];
        \View::share('g_php2jsVars',$this->_php2jsVars);

        return view('stories.player', [
            'sessionUser' => $request->user(),
            'stories' => $storiesA,
            'timeline' => $request->user()->timeline,
        ]);
    }

    public function dashboard(Request $request)
    {
        $stories = $request->user()->timeline->stories;
        $storiesA = $stories->map( function($item, $iter) {
            $a = $item->toArray();
            if ( count($item->mediaFiles) ) {
                $fn = $item->mediaFiles[0]->filename;
                $a['mf_filename'] = $fn;
                $a['mf_url'] = Storage::disk('s3')->url($fn); // %FIXME: use model attribute
            }
            return $a;
        });
        return view('stories.create', [
            'session_user' => $request->user(),
            'timeline' => $request->user()->timeline,
            'stories' => $storiesA,
            'dtoUser' => [
                'avatar' => $request->user()->avatar,
                'fullName' => $request->user()->timeline->name,
                'username' => $request->user()->timeline->username,
            ],
        ]);
    }

}

