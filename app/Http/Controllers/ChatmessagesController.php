<?php
namespace App\Http\Controllers;

use DB;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Resources\ChatmessageCollection;
use App\Http\Resources\Chatmessage as ChatmessageResource;
use App\Models\Chatmessage;
use App\Models\Chatthread;
use App\Models\User;

class ChatmessagesController extends AppBaseController
{
    public function index(Request $request)
    {
        $request->validate([
            // filters
            'chatthread_id' => 'uuid|exists:chathreads,id',
            'sender_id' => 'uuid|exists:users,id',
            'participant_id' => 'uuid|exists:users,id',
            'is_flagged' => 'boolean',
        ]);
        $filters = $request->only(['chatthread_id', 'sender_id', 'is_flagged']) ?? [];

        $query = Chatmessage::query(); // Init query

        // Check permissions
        if ( !$request->user()->isAdmin() ) {
            //$query->where('user_id', $request->user()->id); // non-admin: can only view own...
            //unset($filters['user_id']);
            if ( array_key_exists('chatthread_id', $filters) ) {
                $chatthread = Chatthread::findOrFail($filters['chatthread_id']);
                $this->authorize('view', $chatthread);
            }
            if ( array_key_exists('sender_id', $filters) ) {
                $query->whereHas('chatthread.participants', function($q1) {
                    $q1->where('user_id', $request->user()->id); // limit to threads where session user is a participant
                });
            }
            if ( array_key_exists('participant_id', $filters) ) {
                $query->whereHas('chatthread.participants', function($q1) {
                    $q1->where('user_id', $request->user()->id); // limit to threads where session user is a participant
                });
            }
        }

        // Apply filters
        foreach ($filters as $key => $v) {
            switch ($key) {
            case 'sender_id':
                $query->where('sender_id', $v); // %FIXME: if non-admin limit 
                break;
            case 'participant_id':
                $query->whereHas('chatthread.participants', function($q1) use($key, $v) {
                    $q1->where('user_id', $v);
                });
                break;
            default:
                $query->where($key, $v);
            }
        }

        $data = $query->paginate( $request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)) );
        return new ChatmessageCollection($data);
    }


}
