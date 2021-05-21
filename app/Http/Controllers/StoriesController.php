<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\StoryCollection;
use App\Http\Resources\Story as StoryResource;
use App\Models\Mediafile;
use App\Models\Diskmediafile;
use App\Models\Setting;
use App\Models\Story;
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
        $request['attrs'] = json_decode($request['attrs'], true); // decode 'complex' data

        $vrules = [
            'attrs' => 'required|array',
            'attrs.stype' => 'in:'.StoryTypeEnum::getKeysCsv(), // %TODO : apply elsewhere
            //'timeline_id' => 'required|uuid|exists:timelines',
        ];
        if ( $request->has('mediafile') ) {
            if ( $request->hasFile('mediafile') ) {
                //$vrules['mediafile'] = 'required_if:attrs.stype,photo|file';
                $vrules['mediafile'] = 'file|required_if:attrs.stype,'.StoryTypeEnum::PHOTO;
                // %TODO VIDEO stype
            } else {
                //$vrules['mediafile'] = 'required_if:attrs.stype,photo|uuid|exists:mediafiles,id'; // must be fk to [mediafiles]
                $vrules['mediafile'] = 'uuid|exists:mediafiles,id|required_if:attrs.stype,'.StoryTypeEnum::PHOTO; // must be fk to [mediafiles]
                // %TODO VIDEO stype
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

                if ( $request->attrs['stype'] === StoryTypeEnum::PHOTO ) {
                    if ( $request->hasFile('mediafile') ) {
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
                    } else {
                        // mediafile request param is ID, referneces existing mediafile (in vault)...see vrules above
                        $refMF = Mediafile::where('resource_type', 'vaultfolders')
                            ->where('is_primary', true)
                            ->findOrFail($request->mediafile)
                            ->diskmediafile->createReference(
                                'stories',    // $resourceType
                                $story->id,  // $resourceID
                                'New Story', // $mfname - could be optionally passed as a query param %TODO
                                MediafileTypeEnum::STORY // $mftype
                            );
                    }
                }
                return $story;
            });
        } catch (Exception $e) {
             Log::error( json_encode([
                 'msg' => 'StoriessController::store() - error',
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
        /*
        return response()->json([
            'story' => $story,
        ]);
         */
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

}

