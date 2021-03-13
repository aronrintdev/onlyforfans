<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\PostTypeEnum;
use App\Models\Post as PostModel;

class Post extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();
        $model = PostModel::find($this->id); // %FIXME: n+1 performance issue (not so bad if paginated?)
        $hasAccess = $sessionUser->can('contentView', $model);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'type' => $this->type,
            'price' => $this->price,
            'postable_id' => $this->postable_id,
            'postable_type' => $this->postable_type,
            'timeline_slug' => $this->timeline->slug, // needed for links
            'description' =>  $this->when($hasAccess, $this->description),

            //'mediafiles' =>  $this->when($hasAccess, $this->mediafiles),
            // %TODO %NOTE vs above, we depend here on the caller not loading mediafiles relation where they shouldn't have access (eventually we want to send a blurred image in place when no access)
            'mediafiles' =>  $this->whenLoaded('mediafiles'), 

            'access' =>  $hasAccess,
            'user' =>  $this->user, // $this->when($hasAccess, $this->user),
            'timeline' =>  $this->timeline->load('avatar', 'cover'), // $this->when($hasAccess, $this->user),
            // https://laravel.com/docs/8.x/eloquent-resources#conditional-relationships
            //'mediafiles' =>  $this->when( $hasAccess, MediafileResource::collection($this->whenLoaded('mediafiles')) ), // ??
            'created_at' => $this->created_at,
            'stats' => [
                //'isLikedByMe' => $sessionUser->likedposts->contains($this->id),
                'isLikedByMe' => $this->isLikedByMe,
                'likeCount' => $this->likes->count(),
                'commentCount' => $this->comments->count(),
            ],
        ];
    }
}