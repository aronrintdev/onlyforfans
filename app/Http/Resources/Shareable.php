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
        $is_favorited = null;

        if (isset($request->isFollowers)) {
            $is_favorited = count($this->whenLoaded('sharee')->timeline->favorites->where('user_id', $this->whenLoaded('shareable')->user_id));
        } else if (isset($request->isFollowing)) {
            $is_favorited = count($this->whenLoaded('shareable')->favorites->where('user_id', $this->sharee_id));
        }

        return [
            'id' => $this->id,
            'shareable_id' => $this->shareable_id,
            'is_approved' => $this->is_approved,
            'access_level' => $this->access_level,
            'shareable_type' => $this->shareable_type,
            'sharee_id' => $this->sharee_id,
            'sharee' => $this->whenLoaded('sharee'),
            'sharee_timeline_id' => $this->whenLoaded('sharee')->timeline->id,
            'sharee_timeline_slug' => $this->whenLoaded('sharee')->timeline->slug,
            'shareable' => $this->whenLoaded('shareable'),
            'is_favorited' => $is_favorited,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
        ];
    }
}
