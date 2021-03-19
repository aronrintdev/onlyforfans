<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\StoryCollection;
use App\Models\Mediafile;
use App\Models\Setting;
use App\Models\Story;
use App\Models\Timeline;
use App\Enums\MediafileTypeEnum;
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
                    if ( $request->user()->can('indexStories', $timeline) ) { // should include followers & owner (!)
                        break; // allowed
                    }
                }
                abort(403); // none of the above match, therefore unauthorized
            } while(0);
        }

        $query = Story::query()->with('mediafiles');

        foreach ( $filters as $k => $v ) {
            switch ($k) {
            case 'following':
                $query->whereHas('timeline', function($q1) use(&$request) {
                    $q1->whereIn('id', $request->user()->followedtimelines->pluck('id'));
                });
                break;
            default:
                $query->where($k, $v);
            }
        }

        $data = $query->paginate( $request->input('take', env('MAX_STORIES_PER_REQUEST', 10)) );
        return new StoryCollection($data);
    }

    public function store(Request $request)
    {
        $request['attrs'] = json_decode($request['attrs'], true); // decode 'complex' data

        $vrules = [
            'attrs' => 'required',
            'attrs.stype' => 'required|in:text,photo',
            //'timeline_id' => 'required|uuid|exists:timelines',
        ];
        if ( $request->has('mediafile') ) {
            if ( $request->hasFile('mediafile') ) {
                $vrules['mediafile'] = 'required_if:attrs.stype,photo|file';
            } else {
                $vrules['mediafile'] = 'required_if:attrs.stype,photo|uuid|exists:mediafiles,id'; // must be fk to [mediafiles]
            }
        }
        
        $this->validate($request, $vrules);

        // policy check is redundant as a story is always created on session user's
        //   timeline, however in the future we may be more flexible, or support
        //   multiple timelines which will require request->timeline_id
        //$timeline = Timeline::find($request->user()->timeline_id);
        $timeline = $request->user()->timeline;
        $this->authorize('update', $timeline);

        try {
            $story = DB::transaction(function () use(&$request, &$timeline) {

                $story = Story::create([
                    'timeline_id' => $timeline->id,
                    'content' => $request->attrs['content'] ?? null,
                    'cattrs' => [
                        'background-color' => array_key_exists('bgcolor', $request->attrs) ? $request->attrs['bgcolor'] : '#fff',
                    ],
                    'stype' => $request->attrs['stype'],
                ]);

                if ( $request->attrs['stype'] === 'photo' ) {
                    if ( $request->hasFile('mediafile') ) {
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
                    } else {
                        $src = Mediafile::find($request->mediafile);
                        $cloned = $src->doClone('stories', $story->id);
                        $story->mediafiles()->save($cloned);
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
        $story->mediafiles->each( function($mf) {
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
            if ( count($item->mediafiles) ) {
                $fn = $item->mediafiles[0]->filename;
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
            if ( count($item->mediafiles) ) {
                $fn = $item->mediafiles[0]->filename;
                $a['mf_filename'] = $fn;
                $a['mf_url'] = Storage::disk('s3')->url($fn); // %FIXME: use model attribute
            }
            return $a;
        });
        return [
            'stories' => $storiesA,
            'dtoUser' => [
                'avatar' => $request->user()->avatar,
                'fullname' => $request->user()->timeline->name,
                'username' => $request->user()->timeline->username,
            ],
        ];
    }

}

