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
            'is_free_follower'=> 'boolean',
            'is_favorite'     => 'boolean',
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
            'is_free_follower',
            'is_favorite',
        ]) ?? [];

        $orderBy = 'desc';
        if (isset($request->asc)){
            $orderBy = 'asc';
        }

        $query = Chatthread::query(); // Init query

        // Always two participants in chatthread
        $query->withCount('participants')->having('participants_count', '=', 2);

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
            case 'is_free_follower':
                $query->whereHas('participants', function($q1) use(&$sessionUser) {
                    $q1->whereHas('followedtimelines', function($q2) use(&$sessionUser) {
                        $q2->where('timelines.id', $sessionUser->timeline->id)
                            ->where('access_level', ShareableAccessLevelEnum::DEFAULT);
                    });
                });
                break;
            case 'is_following':
                $query->whereHas('participants', function($q1) use(&$sessionUser) {
                    $q1->whereHas('timeline', function($q2) use(&$sessionUser) {
                        $q2->whereIn('timelines.id', $sessionUser->followedtimelines->pluck('id'));
                    });
                });
                break;
            case 'is_favorite':
                $query->whereHas('participants', function($q1) use(&$sessionUser) {
                    $q1->whereHas('timeline', function($q2) use(&$sessionUser) {
                        $q2->whereIn('timelines.id', $sessionUser->favorites->pluck('favoritable_id'));
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
        case 'amountSpent':
            $query->addSelect(['totalSpent' => Chatmessage::selectRaw('sum(price) as total')
                ->whereColumn('chatthread_id', 'chatthreads.id')
                ->where('sender_id', $sessionUser->id)
                ->groupBy('chatthread_id')
            ])
            ->orderBy('totalSpent', $orderBy);
            break;
        case 'unread':
            $query->addSelect(['unread_count' => Chatmessage::selectRaw('COUNT(*)')
                ->whereColumn('chatthread_id', 'chatthreads.id')
                ->where('sender_id', '<>', $sessionUser->id)
                ->where('is_read', 0)
            ])
            ->orderBy('unread_count', $orderBy);
            break;
        case 'unreadWithTips':
            $query->addSelect(['unread_tips_count' => Chatmessage::selectRaw('COUNT(*)')
                ->whereColumn('chatthread_id', 'chatthreads.id')
                ->where('sender_id', '<>', $sessionUser->id)
                ->where('purchase_only', 1)
                ->where('is_read', 0)
            ])
            ->orderBy('unread_tips_count', $orderBy);
            break;
        case 'online':
            $query
                ->join('chatthread_user', 'chatthread_user.chatthread_id', '=', 'chatthreads.id')
                ->select('chatthreads.*')
                ->join('users', 'chatthread_user.user_id', '=', 'users.id')
                ->where('users.id', '<>', $sessionUser->id)
                ->orderBy('users.is_online', $orderBy)
                ->orderBy('users.last_logged', $orderBy);
            break;
        case 'recent':
        case 'unread-first':
        default:
            $query->orderBy('updated_at', $orderBy);
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

    // Finds and returns an existing thread, or creates new one, between 2 users; does *not* send message
    // %FIXME: should return 201 if created, 200 if existing
    public function findOrCreateDirect(Request $request)
    {
        $request->validate([
            'originator_id'  => 'required|uuid|exists:users,id',
            'participant_id' => 'required|uuid|exists:users,id',
        ]);

        if ( $request->originator_id === $request->participant_id ) {
            abort(422, 'Originator and participant must differ');
        }

        $originator = User::find($request->originator_id);
        $participant = User::find($request->participant_id);

        $ct = Chatthread::findOrCreateDirectChat($originator, $participant);

        return response()->json([
            'chatthread' => $ct,
        ], 201);
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
            'participants'   => 'required|array', // %FIXME: rename to 'recipients' for clarity
            'participants.*' => 'uuid|exists:users,id',
            'mcontent'       => 'required_without:attachments|string',  // optional first message content
            'deliver_at'     => 'numeric', // optional to pre-schedule delivery of message if present
            'price'          => 'numeric',
            'currency'       => 'required_with:price|size:3',
            'attachments'    => 'required_with:price|array',   // optional first message attachments
        ]);

        [ 'chatthreads' => $chatthreads, 'chatmessages' => $chatmessages, 'chatmessagegroup' => $cmGroup ] = Chatthread::findOrCreateChat(
            $request->user(),               // User      $sender
            $request->originator_id,        // int       $originator_id
            $request->participants,         // array     $participants (array of user ids)
            $request->mcontent??'',         // string    $mcontent
            $request->deliver_at ?? null,   // int       $deliver_at
            $request->attachments ?? null,  // array     $attachments
            $request->price ?? null,        // int       $price
            $request->currency ?? null      // string    $currency
        );

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
    public function addMessage(Request $request, Chatthread $chatthread)
    {
        $request->validate([
            'mcontent'    => 'required_without:attachments|string',
            'price'       => 'numeric',
            'currency'    => 'required_with:price|size:3',
            'attachments' => 'required_with:price|array',
            'deliver_at'  => 'numeric',
        ]);

        $chatmessage = $chatthread->addMessage(
            $request->user(),
            $request->mcontent ?? null, // string $mcontent = ''
            $request->attachments ?? [], // array $attachments = []
            $request->price ?? null, // $price = null
            $request->currency ?? null // $currency = null
        );

        if ($request->has('deliver_at')) {
            $chatmessage->schedule($request->deliver_at);
        } else {
            $chatmessage->deliver();
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
