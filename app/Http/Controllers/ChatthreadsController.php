<?php
namespace App\Http\Controllers;

use App\Enums\ShareableAccessLevelEnum;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Resources\ChatthreadCollection;
use App\Http\Resources\Chatthread as ChatthreadResource;
//use App\Http\Resources\ChatmessageCollection;
use App\Http\Resources\Chatmessage as ChatmessageResource;
use App\Events\MessageSentEvent;
use App\Models\Chatmessage;
use App\Models\Chatthread;
use App\Models\User;

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

        // Check permissions, restrict to session user if non-admin
        if ( !$request->user()->isAdmin() ) {
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

        $data = $query->paginate( $request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)) );
        return new ChatthreadCollection($data); 
    }

    /**
     *
     * @param Request $request
     * @param Chatthread $chatthread
     * @return ChatthreadResource
     */
    public function show(Request $request, Chatthread $chatthread)
    {
        /*
        $sessionUser = $request->user();
        dd( 'ctrl', 
            $chatthread->participants->pluck('username'), 
            $sessionUser->username, 
            $chatthread->participants->contains($sessionUser->id) ? 'yes' : 'no'
        );
         */
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
            'originator_id' => 'required|uuid|exists:users,id',
            'participants' => 'required|array', // %FIXME: rename to 'recipients' for clairty
            'participants.*' => 'uuid|exists:users,id',
            'mcontent' => 'string|required_with:deliver_at', // optional first message content
            'deliver_at' => 'numeric', // optional to pre-schedule delivery of message if present
        ]);
        $originator = User::find($request->originator_id);

        $chatthreads = collect();
        foreach ($request->participants as $pkid) {
            // Check if chat with participant already exits
            $ct = $originator->chatthreads()->whereHas('participants', function ($query) use($pkid) {
                $query->where('user_id', $pkid);
            })->first();

            // Start new chat thread if one is not found
            if (!isset($ct)) {
                $ct = Chatthread::startChat($originator);
                $ct->addParticipant($pkid);
            }

            if ( $request->has('mcontent') ) { // if included send the first message
                if ( $request->has('deliver_at') ) {
                    $ct->scheduleMessage($request->user(), $request->mcontent, $request->deliver_at);
                } else {
                    $ct->sendMessage($request->user(), $request->mcontent);
                }
            }
            $ct->refresh();
            $chatthreads->push($ct);
        }

        return response()->json([
            'chatthreads' => $chatthreads,
        ], 201);
    }

    /**
     *
     * @param Request $request
     * @param Chatthread $chatthread
     * @return ChatmessageResource
     */
    public function sendMessage(Request $request, Chatthread $chatthread)
    {
        $request->validate([
            'mcontent' => 'required|string',
        ]);
        $chatmessage = $chatthread->sendMessage($request->user(), $request->mcontent);
        try {
            //broadcast( new MessageSentEvent($chatmessage) )->toOthers();
            MessageSentEvent::dispatch($chatmessage);
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
    public function scheduleMessage(Request $request, Chatthread $chatthread)
    {
        $request->validate([
            'mcontent' => 'required|string',
            //'deliver_at' => 'required|date',
            'deliver_at' => 'required|numeric',
        ]);
        $chatmessage = $chatthread->scheduleMessage($request->user(), $request->mcontent, $request->deliver_at);
        return new ChatmessageResource($chatmessage);
    }

}
