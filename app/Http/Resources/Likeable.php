<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Likeable as LikeableModel;
use App\Http\Resources\Post as PostResource;

class Likeable extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();

        return [
            //'id' => $this->id,
            'likeable_id' => $this->likeable_id,
            'likeable_type' => $this->likeable_type,
            'liker_id' => $this->likee_id, // %FIXME: mis-named column should be liker_id AF-269
            //'likeable' => PostResource($this->whenLoaded('likeable')),
            'likeable' => $this->whenLoaded('likeable'),
            'liker' => $this->whenLoaded('liker'),
            'created_at' => $this->created_at,
        ];
    }
}
