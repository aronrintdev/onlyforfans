<?php
namespace App\Http\Controllers;

use Exception;
use DB;
use Illuminate\Support\Facades\Config;
use App\Models\User;
use App\Models\Favorite;
use App\Models\Mediafile;
use App\Models\Mycontact;
use App\Models\Chatthread;
use App\Models\Chatmessagegroup;
use App\Models\Chatmessage;
use Illuminate\Http\Request;
use App\Events\MessageSentEvent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Notifications\MessageReceived;
use App\Enums\ShareableAccessLevelEnum;
use App\Enums\MessagegroupTypeEnum;
use App\Http\Resources\ChatthreadCollection;
use App\Http\Resources\Chatthread as ChatthreadResource;
use App\Http\Resources\Chatmessage as ChatmessageResource;

class ChatthreadsController extends AppBaseController
{

    /**
     * 
     * @param Request $request
     * @return ChatthreadCollection
     */
    public function index(Request $request)
    {
        $sessionUser = $request->user();

        $request->validate([
            // filters
            'originator_id'   => 'uuid|exists:users,id',
            'participant_id'  => 'uuid|exists:users,id',
            'is_tip_required' => 'boolean',
            'is_unread'       => 'boolean',
            'is_subscriber'   => 'boolean',
            'is_following'    => 'boolean',
        ]);

        // Filters
        $filters = $request->only([
            // values
            'originator_id',
            'participant_id',
            // booleans
            'is_tip_required',
            'is_unread',
            'is_subscriber',
            'is_following',
        ]) ?? [];

        if ( $request->has('sortBy') ) { // UI may imply these filters when sorting
            switch ($request->sortBy) {
            case 'unread-first':
            case 'oldest-unread-first':
                $filters['is_unread'] = 1;
                break;
            }
        }

        $query = Chatthread::query(); // Init query

        // If user is admin and originator or participant ids are not specified then can perform query where user is
        // not a part of the participants
        if ( true || 
            !(
                $request->user()->isAdmin() &&
                ($request->has('originator_id') || $request->has('participant_id'))
            )
        ) {
            $query->whereHas('participants', function($q1) use(&$request) {
                $q1->where('users.id', $request->user()->id); // limit to threads where session user is a participant
            });
        }

        // Apply filters
        foreach ($filters as $key => $v) {
            switch ($key) {
            case 'originator_id':
                $query->where('originator_id', $v);
                break;
            case 'participant_id':
                $query->whereHas('participants', function($q1) use($v) {
                    $q1->where('users.id', $v);
                });
                break;
            case 'is_unread':
                $v = $v ? 0 : 1; // invert: is_unread -> is_read
                $query->whereHas('chatmessages', function($q1) use($v) {
                    $q1->where('is_read', $v); // apply filter
                });
                break;
            case 'is_subscriber': // %TODO
                $query->whereHas('participants', function($q1) use(&$sessionUser) {
                    $q1->whereHas('subscribedtimelines', function($q2) use(&$sessionUser) {
                        $q2->where('timelines.id', $sessionUser->timeline->id);
                    });
                });
                break;
            case 'is_following':
                $query->whereHas('participants', function($q1) use(&$sessionUser) {
                    $q1->whereHas('followedtimelines', function($q2) use(&$sessionUser) {
                        $q2->where('timelines.id', $sessionUser->timeline->id)
                            ->where('access_level', ShareableAccessLevelEnum::DEFAULT);
                    });
                });
                break;
            default:
                $query->where($key, $v);
            }
        }

        // Sorting
        switch ($request->sortBy) {
        case 'oldest':
        case 'oldest-unread-first':
            $query->orderBy('updated_at', 'asc');
            break;
        case 'recent':
        case 'unread-first':
        default:
            $query->orderBy('updated_at', 'desc');
            //$query->latest();
        }

        $data = $query->paginate( $request->input('take', Config::get('collections.defaultMax', 10)));
        return new ChatthreadCollection($data);
    }

    /**
     * Simple search
     *
     * @param Request $request
     * @return ChatthreadCollection
     */
    public function search(Request $request)
    {
        $searchQuery = $request->input('query') ?? $request->input('q');

        $data = Chatthread::search($searchQuery)->where('participants', $request->user()->getKey())
            ->paginate($request->input('take', Config::get('collections.defaultMax', 10)));

        return new ChatthreadCollection($data);
    }

    /**
     * @param Request $request
     * @return array Total unread count for the current user
     */
    public function getTotalUnreadCount(Request $request)
    {
        $userId = $request->user()->id;

        $chatthreads = Chatthread::whereHas('participants', function($q) use($userId) {
            $q->where('users.id', $userId);
        })->get();

        $total = 0;
        foreach ($chatthreads as $ct) {
            $count = $ct->chatmessages()->where([
                ['is_read', '=', 0],
                ['sender_id', '<>', $userId]
            ])->count();
            $total += $count;
        }

        return response()->json(
            ['total_unread_count' => $total]
        );
    }

    /**
     * @param Request $request
     */
    public function markAllRead(Request $request)
    {
        $userId = $request->user()->id;

        $chatthreads = Chatthread::whereHas('participants', function($q) use($userId) {
            $q->where('users.id', $userId);
        })->get();

        foreach ($chatthreads as $ct) {
            $ct->chatmessages()->where([
                ['is_read', '=', 0],
                ['sender_id', '<>', $userId]
            ])->update(['is_read' => 1]);
        }

        http_response_code(200);
    }

    /**
     * @param Request $request
     * @param Chatthread $chatthread
     */
    public function markRead(Request $request, Chatthread $chatthread)
    {
        $userId = $request->user()->id;

        $this->authorize('view', $chatthread);

        $chatthread->chatmessages()->where([
            ['is_read', '=', 0],
            ['sender_id', '<>', $userId]
        ])->update(['is_read' => 1]);

        http_response_code(200);
    }

    /**
     * @param Request $request
     * @param Chatthread $chatthread
     */
    public function getMuteStatus(Request $request, Chatthread $chatthread)
    {
        $this->authorize('view', $chatthread);

        $sessionUser = $request->user();
        $participant = $chatthread->participants()->find($sessionUser->id);

        if (!$participant) {
            // user is not part of the chatthread, so abort
            abort(403);
        }

        return response()->json([
            'is_muted' => $participant->pivot->is_muted,
        ]);
    }

    /**
     * @param Request $request
     * @param Chatthread $chatthread
     */
    public function toggleMute(Request $request, Chatthread $chatthread)
    {
        $request->validate([
            'is_muted' => 'required|boolean',
        ]);

        $this->authorize('view', $chatthread);

        $sessionUser = $request->user();
        $participant = $chatthread->participants()->find($sessionUser->id);

        if (!$participant) {
            // user is not part of the chatthread, so abort
            abort(403);
        }

        $participant->pivot->is_muted = $request->is_muted;
        $participant->pivot->save();

        http_response_code(200);
    }

    /**
     *
     * @param Request $request
     * @param Chatthread $chatthread
     * @return ChatthreadResource
     */
    public function show(Request $request, Chatthread $chatthread)
    {
        $this->authorize('view', $chatthread);
        return new ChatthreadResource($chatthread);
    }

    // %HERE
    // %NOTE: May create more than a single chatthread
    /**
     * Stores a new chatthread
     *
     * @param Request
     */
    public function store(Request $request)
    {
        $request->validate([
            'originator_id'  => 'required|uuid|exists:users,id',
            'participants'   => 'required|array', // %FIXME: rename to 'recipients' for clairty
            'participants.*' => 'uuid|exists:users,id',
            'mcontent'       => 'required_without:attachments|string',  // optional first message content
            'deliver_at'     => 'numeric', // optional to pre-schedule delivery of message if present
            'price'          => 'numeric',
            'currency'       => 'required_with:price|size:3',
            'attachments'    => 'required_with:price|array',   // optional first message attachments
        ]);

        $rattrs = (object)$request->all();

        ['chatthreads'=>$chatthreads, 'chatmessages'=>$chatmessages, 'chatmessagegroup'=>$cmGroup] = Chatthread::findOrCreateChat($request->user(), $rattrs);

        return response()->json([
            'chatthreads' => $chatthreads,
            'chatmessages' => $chatmessages,
            'chatmessagegroup' => $cmGroup,
        ], 201);
    }

    /**
     *
     * @param Request $request
     * @param Chatthread $chatthread
     * @return ChatmessageResource
     */
    // %TODO: refactor to a model file
    public function sendMessage(Request $request, Chatthread $chatthread)
    {
        $request->validate([
            'mcontent'    => 'required_without:attachments|string',
            'price'       => 'numeric',
            'currency'    => 'required_with:price|size:3',
            'attachments' => 'required_with:price|array',
        ]);

        $rattrs = (object)$request->all();

        // Create new chat message
        $chatmessage = $chatthread->sendMessage($request->user(), $rattrs);

        try {
            //broadcast( new MessageSentEvent($chatmessage) )->toOthers();
            MessageSentEvent::dispatch($chatmessage);

            // send notification
            foreach($chatthread->participants as $participant) {
                // don't send notification to myself
                if ($participant->id === $request->user()->id) {
                    continue;
                }

                // check is_muted pivot field ON/OFF state
                if (!$participant->pivot->is_muted) {
                    $participant->notify( new MessageReceived($chatmessage, $request->user()) );
                }
            }
        } catch( Exception $e ) {
            Log::warning('ChatthreadsController::sendMessage().broadcast', [
                'msg' => $e->getMessage(),
            ]);
        }
        return new ChatmessageResource($chatmessage);
    }

    /**
     *
     * @param Request $request
     * @param Chatthread $chatthread
     * @return ChatmessageResource
     */
    // %TODO: refactor to a model file
    public function scheduleMessage(Request $request, Chatthread $chatthread)
    {
        $request->validate([
            'mcontent' => 'required_without:attachments|string',
            'attachments' => 'array',
            //'deliver_at' => 'required|date',
            'deliver_at' => 'required|numeric', // unix timestamp in seconds utc (%TODO %CHECKME)
        ]);
        $chatmessage = $chatthread->scheduleMessage($request->user(), $request->mcontent, $request->deliver_at);

        // Create mediafile refs for any attachments
        if ($request->has('attachments')) {
            foreach ($request->attachments as $a) {
                if ($a['diskmediafile_id']) {
                    Mediafile::find($a['id'])->diskmediafile->createReference(
                        $chatmessage->getMorphString(), // string   $resourceType
                        $chatmessage->getKey(),         // int      $resourceID
                        $a['mfname'],                   // string   $mfname
                        'messages'                      // string   $mftype
                    );
                }
            }
        }
        return new ChatmessageResource($chatmessage);
    }

    /**
     * Create and put message in draft status,This will allow for attachments to be given to it.
     * @param Request $request
     * @param Chatthread $chatthread
     * @return ChatmessageResource
     */
    public function draft(Request $request, Chatthread $chatthread)
    {
        //
    }

    public function favorite(Request $request, Chatthread $chatthread)
    {
        $this->authorize('favorite', $chatthread);

        $favorite = Favorite::create([
            'user_id' => $request->user()->id,
            'favoritable_type' => $chatthread->getMorphString(),
            'favoritable_id' => $chatthread->id,
        ]);

        $chatthread->refresh();
        return new ChatthreadResource($chatthread);
    }

}
