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

        return [
            'id' => $this->id,
            'originator_id' => $this->originator_id,
            'is_tip_required' => $this->is_tip_required,
            //'chatmessages' => $this->chatmessages,
            'chatmessages' => $this->chatmessages()->latest()->take(1)->get(), // limit to 1, for preview only, save bw
            'msg_count' => $this->chatmessages()->count(),
            'participants' => $this->participants,
            'originator' => $this->originator,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
