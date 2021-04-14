<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Sharable as SharableModel;
use App\Http\Resources\Post as PostResource;

class Sharable extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();

        return [
            //'id' => $this->id,
            'shareable_id' => $this->shareable_id,
            'shareable_type' => $this->shareable_type,
            'sharee_id' => $this->sharee_id,
            'sharee' => $this->whenLoaded('sharee'),
            'shareable' => $this->whenLoaded('shareable'),
            'created_at' => $this->created_at,
        ];
    }
}
