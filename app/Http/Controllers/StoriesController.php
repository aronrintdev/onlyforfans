<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;

use App\Http\Resources\StoryCollection;
use App\Http\Resources\Story as StoryResource;
use App\Models\Mediafile;
use App\Models\Diskmediafile;
use App\Models\Setting;
use App\Models\Story;
use App\Models\Storyqueue;
use App\Models\Timeline;
use App\Enums\MediafileTypeEnum;
use App\Enums\StoryTypeEnum;

class StoriesController extends AppBaseController
{

    // this is the general REST version. There should also be timelines/{timeline}/stories (?)
    // %FIXME : remove filters...GET params are not nested
    public function index(Request $request)
    {
        $vrules = [
            'timeline_id' => 'uuid|exists:timelines,id',
            'following' => 'boolean',
        ];
        if ( $request->has('stypes') ) {
            if ( is_array($request->stypes) ) {
                $vrules['stypes'] = 'array';
                $vrules['stypes.*'] = 'in:'.StoryTypeEnum::getKeysCsv();
            } else {
                $vrules['stypes'] = 'in:'.StoryTypeEnum::getKeysCsv();
            }
        }

        $request->validate($vrules);
        $filters = $request->only([ 'stypes', 'timeline_id', 'following' ]) ?? [];

        // Init query
        $query = Story::query()->with('mediafiles');

        // Check permissions
        if ( !$request->user()->isAdmin() ) {
            do {
                if ( array_key_exists('timeline_id', $filters) ) {
                    $timeline = Timeline::findOrFail($filters['timeline_id']);
                    if ( $request->user()->can('indexStories', $timeline) ) { // should include followers & owner (!)
                        break; // allowed
                    }
                } else {
                    $filters['following'] = true; // non-admin: force to following only
                    break; // allowed
                }
                abort(403); // none of the above match, therefore unauthorized
            } while(0);
        }

        // Apply filters
        foreach ( $filters as $k => $f ) {
            switch ($k) {
            case 'following':
                $query->whereHas('timeline', function($q1) use(&$request) {
                    $q1->whereIn('id', $request->user()->followedtimelines->pluck('id'));
                });
                break;
            case 'stypes':
                if ( is_array($filters['stypes']) ) {
                    $query->whereIn('stype', $filters['stypes']);
                } else {
                    $query->where('stype', $filters['stypes']);
                }
                break;
            default:
                $query->where($k, $f);
            }
        }

        $data = $query->paginate( $request->input('take', env('MAX_STORIES_PER_REQUEST', 10)) );
        return new StoryCollection($data);
    }

    public function store(Request $request)
    {
        $vrules = [
            'stype' => 'in:'.StoryTypeEnum::getKeysCsv(),
            //'timeline_id' => 'required|uuid|exists:timelines',
        ];
        if ( $request->has('mediafile_id') ) {
            $vrules['mediafile_id'] = 'uuid|exists:mediafiles,id';
            $vrules['stype'] = 'string|in:'.StoryTypeEnum::PHOTO;
        } else if ( $request->hasFile('mediafile') ) {
            //$vrules['mediafile'] = 'required_if:attrs.stype,photo|file';
            $vrules['mediafile'] = 'file|required_if:stype,'.StoryTypeEnum::PHOTO;
            // %TODO VIDEO stype
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
                    'content' => $request->content ?? null,
                    'swipe_up_link' => $request->link ?? null,
                    'cattrs' => [
                        'background-color' => $request->bgcolor ?? '#fff',
                    ],
                    'stype' => $request->stype,
                ]);

                if ( $request->stype === StoryTypeEnum::PHOTO ) {
                    if ( $request->has('mediafile_id') ) {
                        // add to story timeline using a file from the vault
                        $mediafile = Mediafile::find($request->mediafile_id);
                        $this->authorize('update', $mediafile);
                        $mediafile->diskmediafile->createReference(
                            'stories',                                      // string   $resourceType
                            $story->id,                                     // int      $resourceID
                            $request->input('mfname', $mediafile->mfname),  // string   $mfname
                            MediafileTypeEnum::STORY                        // string   $mftype
                        );
                    } else if ( $request->hasFile('mediafile') ) {
                        // mediafile request param is of type FILE...see vrules above
                        $file = $request->file('mediafile');
                        $subFolder = $request->user()->id;
                        $s3Path = $file->store('./'.$subFolder, 's3'); // %FIXME: hardcoded
                        $mediafile = Diskmediafile::doCreate([
                            'owner_id'        => $request->user()->id,
                            'filepath'        => $s3Path,
                            'mimetype'        => $file->getMimeType(),
                            'orig_filename'   => $file->getClientOriginalName(),
                            'orig_ext'        => $file->getClientOriginalExtension(),
                            'mfname'          => $file->getClientOriginalName(),
                            'mftype'          => MediafileTypeEnum::STORY,
                            'resource_id'     => $story->id,
                            'resource_type'   => 'stories',
                        ]);
                    }
                }
                return $story;
            });
        } catch (Exception $e) {
             Log::error( json_encode([
                 'msg' => 'StoriesController::store() - error',
                 'emsg' => $e->getMessage(),
             ]) );
            abort(400);
        }

        return response()->json([
            'story' => $story,
        ], 201);
    }

    public function show(Request $request, Story $story)
    {
        $this->authorize('view', $story);
        return new StoryResource($story);
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
                $a['mf_filename'] = $item->mediafiles[0]->name;
                $a['mf_url'] = $item->mediafiles[0]->filepath;
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

    public function getSlides(Request $request)
    {
        $vrules = [
            'viewer_id' => 'required|uuid|exists:users,id',
            'timeline_id' => 'required|uuid|exists:timelines,id',
        ];
        $this->validate($request, $vrules);
        
        $daysWindow = env('STORY_WINDOW_DAYS', 10000);
        $storyqueues = Storyqueue::with(['story.mediafiles'])
            ->where('viewer_id', $request->viewer_id)
            ->where('timeline_id', $request->timeline_id)
            ->where('created_at','>=',Carbon::now()->subDays($daysWindow))
            ->orderBy('created_at', 'asc') // sort slides relation oldest first
            ->get();
        $slideIndex = 0;
        $stories = $storyqueues->map( function($sq) use(&$slideIndex) {
            if ( $sq->viewed_at !== null ) {
                $slideIndex +=1;
            }
            return $sq->story;
        });
        return response()->json([
            'stories' => $stories,
            'slideIndex' => $slideIndex,
        ]);
    }

    public function markViewed(Request $request)
    {
        $this->validate($request, [
            'viewer_id' => 'required|uuid|exists:users,id',
            'story_id' => 'required|uuid|exists:storyqueues,story_id',
            //'story_id' => 'required|uuid|exists:stories,id',
        ]);
        $sq = Storyqueue::where('viewer_id', $request->viewer_id)
            ->where('story_id', $request->story_id)
            ->first();
        if ($sq) {
            $sq->viewed_at = Carbon::now();
            $sq->save();
        } else {
            // %TODO log error
        }
        return response()->json([ 'storyqueue' => $sq ]);
    }

}

