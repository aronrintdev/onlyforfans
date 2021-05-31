<?php
namespace App\Http\Controllers;

use DB;
use Exception;
use Throwable;
use Illuminate\Broadcasting\InteractsWithSockets;
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
    public function index(Request $request)
    {
        $request->validate([
            // filters
            'originator_id' => 'uuid|exists:users,id',
            'participant_id' => 'uuid|exists:users,id',
            'is_tip_required' => 'boolean',
        ]);
        $filters = $request->only(['originator_id', 'participant_id', 'is_tip_required']) ?? [];

        $query = Chatthread::query(); // Init query

        // Check permissions
        if ( !$request->user()->isAdmin() ) {
            $query->whereHas('participants', function($q1) use(&$request) {
                $q1->where('user_id', $request->user()->id); // limit to threads where session user is a participant
            });
        }

        // Apply filters
        foreach ($filters as $key => $v) {
            switch ($key) {
            case 'originator_id':
                $query->where('originator_id', $v);
                break;
            case 'participant_id':
                $query->whereHas('participants', function($q1) use($key, $v) {
                    $q1->where('user_id', $v);
                });
                break;
            default:
                $query->where($key, $v);
            }
        }

        $data = $query->latest()->paginate( $request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)) );
        return new ChatthreadCollection($data); 
    }

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

    public function store(Request $request) 
    {
        $request->validate([
            'originator_id' => 'required|uuid|exists:users,id',
            'participants' => 'required|array',
            'participants.*' => 'uuid|exists:users,id',
        ]);
        $originator = User::find($request->originator_id);
        $chatthread = Chatthread::startChat($originator);

        foreach ($request->participants as $pkid) {
            $chatthread->addParticipant($pkid);
        }

        return new ChatthreadResource($chatthread);
    }

    public function sendMessage(Request $request, Chatthread $chatthread)
    {
        $request->validate([
            'mcontent' => 'required|string',
        ]);
        $chatmessage = $chatthread->sendMessage($request->user(), $request->mcontent);
        broadcast( new MessageSentEvent($chatmessage) )->toOthers();
        return new ChatmessageResource($chatmessage);
    }

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
