<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Chatmessagegroup as ChatmessagegroupModel;
use App\Enums\MessagegroupTypeEnum;

class Chatmessagegroup extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();
        $model = ChatmessagegroupModel::find($this->id);
        //$hasAccess = $sessionUser->can('view', $model);

        $attachmentCounts = (function() {
            $result = [
                'images_count' => 0,
                'videos_count' => 0,
                'audios_count' => 0,
            ];
            $firstCM = $this->chatmessages[0];
            foreach ($firstCM->mediafiles ?? [] as $a) {
                if ($a->is_image) {
                    $result['images_count'] += 1;
                }
                if ($a->is_video) {
                    $result['videos_count'] += 1;
                }
                if ($a->is_audio) {
                    $result['audios_count'] += 1;
                }
            }
            return $result;
        })();

        return [
            'id' => $this->id,
            'sender_id' => $this->sender_id,
            //'sender' => $this->sender,
            'mgtype' => $this->mgtype,

            'price' => $this->price ?? '',
            'currency' => $this->currency ?? '',
            'mcontent' => $this->mcontent ?? '',
            'sender_name' => $this->cattrs['sender_name'] ?? '',
            'deliver_at' => $this->cattrs['deliver_at'] ?? '',
            'participant_count' => array_key_exists('participants', $this->cattrs['participants']??[])
                ? count($this->cattrs['participants'])
                : null,
            'attachment_counts' => $attachmentCounts,
            'sent_count' => $this->chatmessages->count(),
            'read_count' => $this->chatmessages()->where('is_read', true)->count(),
            'purchased_count' => '??',
            'scheduled_count' => 'tbd',
            'delivered_count' => 'tbd',
            'verified_count' => 'tbd',

            'cattrs' => $this->cattrs,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

    }
}
