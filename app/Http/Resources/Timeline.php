<?php
namespace App\Http\Resources;

use App\Models\Casts\Money as CastsMoney;
use App\Models\Timeline as TimelineModel;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'price' => CastsMoney::doSerialize($this->price),
            'price_display' => TimelineModel::formatMoney($this->price),
            'is_follow_for_free' => $this->is_follow_for_free,
            'verified' => $this->verified,
            'avatar' => $this->avatar ? $this->avatar : $this->user->avatar,
            'cover' => $this->cover ? $this->cover : $this->user->cover,
            'description' =>  $this->when($hasAccess, $this->description),
            'mediafiles' =>  $this->when($hasAccess, $this->mediafiles),
            'stories' =>  $this->when($hasAccess, $this->stories),
            //'storyqueues' =>  $this->when($hasAccess, $this->storyqueues), // if included, limit to viewers?
            'user' => $this->user,
            //'campaign' => $this->user->campaign,
            'userstats' => $this->user->getStats(),
            'is_owner' => $sessionUser->id === $this->user->id,
            'is_following' => $this->followers->contains($sessionUser->id),
            'is_subscribed' => $this->subscribers->contains($sessionUser->id),
            'is_favorited' => count($this->favorites->where('user_id', $sessionUser->id)),
            'is_storyqueue_empty' => $this->isStoryqueueEmpty(),
            // https://laravel.com/docs/8.x/eloquent-resources#conditional-relationships
            //'mediafiles' =>  $this->when( $hasAccess, MediafileResource::collection($this->whenLoaded('mediafiles')) ), // ??
            'created_at' => $this->created_at,
        ];
    }
}
