<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Timeline as TimelineModel;

class Timeline extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();
        $model = TimelineModel::find($this->id);
        $hasAccess = $sessionUser->can('view', $model);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'about' => $this->about,
            'price' => $this->price,
            'is_follow_for_free' => $this->is_follow_for_free,
            'verified' => $this->verified,
            'avatar' => $this->avatar,
            'cover' => $this->cover,
            'description' =>  $this->when($hasAccess, $this->description),
            'mediafiles' =>  $this->when($hasAccess, $this->mediafiles),
            'userstats' => $sessionUser->getStats(),
            'is_owner' => $sessionUser->id === $this->user->id,
            'is_following' => $this->followers->contains($sessionUser->id),
            'is_subscribed' => $this->subscribers->contains($sessionUser->id),
            // https://laravel.com/docs/8.x/eloquent-resources#conditional-relationships
            //'mediafiles' =>  $this->when( $hasAccess, MediafileResource::collection($this->whenLoaded('mediafiles')) ), // ??
            'created_at' => $this->created_at,
        ];
    }
}
