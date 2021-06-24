<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Chatthread as ChatthreadModel;

class Chatthread extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();
        $model = ChatthreadModel::find($this->id);
        $hasAccess = $sessionUser->can('view', $model);

        // Other participant who is not session user
        $otherUser = $this->participants->filter( function($u) use(&$sessionUser) {
            return $u->id !== $sessionUser->id;
        })->first();
        /*
        dd(
            $this->participants->pluck('username'),
            $sessionUser->username,
            $otherUser->username,
        );
         */

        return [
            'id' => $this->id,
            'originator_id' => $this->originator_id,
            'is_tip_required' => $this->is_tip_required,
            //'chatmessages' => $this->chatmessages,
            'chatmessages' => $this->chatmessages()->latest()->take(1)->get(), // limit to 1, for preview only, save bw
            'msg_count' => $this->chatmessages()->count(),
            'unread_count' => $this->chatmessages()->where('is_read',0)->count(),
            'participants' => $this->participants,
            'has_subscriber' => $sessionUser->timeline->subscribers->contains($otherUser->id), // non-session user participant is a subscriber of session user
            'originator' => $this->originator,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // %NOTE: in the current usage scenario, there are only 2 participants in a chat, 
        // the 'originator' (1st sender), and one another user. Thus 'has_subscriber' 
        // simply indicates if the 'other user' is a subscriber to the session user's 
        // timeline or not 
    }
}
