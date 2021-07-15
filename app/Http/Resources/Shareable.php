<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Shareable as ShareableModel;
use App\Http\Resources\Post as PostResource;

class Shareable extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();

        return [
            //'id' => $this->id,
            'shareable_id' => $this->shareable_id,
            'is_approved' => $this->is_approved,
            'access_level' => $this->access_level,
            'shareable_type' => $this->shareable_type,
            'sharee_id' => $this->sharee_id,
            'sharee' => $this->whenLoaded('sharee'),
            'sharee_slug' => $this->whenLoaded('sharee')->timeline->slug,
            'shareable' => $this->whenLoaded('shareable'),
            'created_at' => $this->created_at,
        ];
    }
}
