<?php
namespace App\Http\Resources;

use App\Models\Tip;
use Illuminate\Support\Collection;
use App\Models\Chatmessage as ChatmessageModel;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class Chatmessage extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();
        if ($sessionUser) {
            $model = ChatmessageModel::find($this->id);
            $isSender = $this->sender_id === $sessionUser->id;
            if ($isSender) {
                $hasAccess = true;
            } else if ( $this->purchase_only ) {
                $hasAccess = $model->checkAccess($sessionUser);
            } else {
                $hasAccess = $sessionUser->can('view', $model);
            }
        } else {
            $hasAccess = false;
            $isSender = false;
        }

        $attachments = new Collection();
        if (isset($this->cattrs)) {
            if (isset($this->cattrs['tip_id'])) {
                $tip = Tip::find($this->cattrs['tip_id']);
                if (isset($tip)) {
                    $attachments->push(
                        Tip::find($this->cattrs['tip_id'])->getMessagableArray()
                    );
                } else {
                    Log::warning('Tip attached to message could not be found', [ 'tip_id' => $this->cattrs['tip_id'], 'message_id' => $this->id ]);
                }
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
            'attachments' => $hasAccess ? $attachments->all() : [], // Only send attachments is user has access
            'mediafile_counts' => [
                'images' => $this->mediafiles->where('is_image', true)->count(),
                'videos' => $this->mediafiles->where('is_video', true)->count(),
                'audios' => $this->mediafiles->where('is_audio', true)->count(),
            ],
            'delivered_at' => $this->delivered_at ?? $this->created_at,
            // 'updated_at' => $this->updated_at,
        ];
    }
}
