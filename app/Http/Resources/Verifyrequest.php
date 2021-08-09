<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Verifyrequest extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id, 
            'guid' => $this->guid, 
            'service_guid' => $this->service_guid, 
            'vservice' => $this->vservice, 
            'vstatus' => $this->vstatus, 
            'requester_id' => $this->requester_id, 
            'requester_username' => $this->requester->username, 
            'last_checked_at' => $this->last_checked_at, 
            'callback_url' => $this->callback_url, 
            'notes' => $this->notes, 
            'cattrs' => $this->cattrs, 
            'created_at' => $this->created_at, 
            'updated_at' => $this->update_at, 
        ];
    }
}
