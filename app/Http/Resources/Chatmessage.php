<?php
namespace App\Http\Resources;

use App\Models\Tip;
use Illuminate\Support\Collection;
use App\Models\Chatmessage as ChatmessageModel;
use Illuminate\Http\Resources\Json\JsonResource;

class Chatmessage extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();
        $model = ChatmessageModel::find($this->id);
        $hasAccess = $sessionUser->can('view', $model);
        $isSender = $this->sender_id === $sessionUser->id;

        $attachments = new Collection();
        if (isset($this->cattrs)) {
            if (isset($this->cattrs['tip_id'])) {
                $attachments->push(
                    Tip::find($this->cattrs['tip_id'])->getMessagableArray()
                );
            }
        }

        foreach($this->mediafiles as $mediafile) {
            $attachments->push(
                $mediafile->getMessagableArray()
            );
        }

        return [
            'id' => $this->id,
            'chatthread_id' => $this->chatthread_id,
            'sender_id' => $this->sender_id,
            'is_sender' => $isSender,
            'mcontent' => $this->mcontent,
            'deliver_at' => $this->deliver_at,
            'purchase_only' => $this->purchase_only,
            'has_access' => $hasAccess,
            'purchased_by' => $this->sharees,
            'price' => $this->price,
            'is_delivered' => $this->is_delivered,
            'is_read' => $this->is_read,
            'is_flagged' => $this->is_flagged,
            'attachments' => $attachments->all(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
