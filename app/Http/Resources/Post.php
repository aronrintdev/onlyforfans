<?php
namespace App\Http\Resources;

use App\Enums\PostTypeEnum;
use App\Models\Casts\Money as CastsMoney;
use App\Models\Post as PostModel;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Mediafile as MediafileResource;

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
            'price_for_followers' => CastsMoney::doSerialize($this->price_for_followers),
            'price_for_subscribers' => CastsMoney::doSerialize($this->price_for_subscribers),
            'price_display_for_followers' => PostModel::formatMoney($this->price_for_followers),
            'price_display_for_subscribers' => PostModel::formatMoney($this->price_for_subscribers),
            'postable_id' => $this->postable_id,
            'postable_type' => $this->postable_type,
            'timeline_slug' => $this->timeline->slug, // needed for links
            'description' =>  $this->when($hasAccess, $this->description),

            //'mediafiles' =>  $this->when($hasAccess, $this->mediafiles),
            // %TODO %NOTE vs above, we depend here on the caller not loading mediafiles relation where they shouldn't have access (eventually we want to send a blurred image in place when no access)
            //'mediafiles' =>  $this->whenLoaded('mediafiles'), 
            'mediafiles' =>  MediafileResource::collection($this->whenLoaded('mediafiles')), 
            'mediafile_count' =>  $this->mediafiles->count(),

            'access' =>  $hasAccess,
            'user' =>  $this->user, // $this->when($hasAccess, $this->user),
            'timeline' =>  $this->timeline->load('avatar', 'cover'), // $this->when($hasAccess, $this->user),
            // https://laravel.com/docs/8.x/eloquent-resources#conditional-relationships
            //'mediafiles' =>  $this->when( $hasAccess, MediafileResource::collection($this->whenLoaded('mediafiles')) ), // ??
            'created_at' => $this->created_at,
            'stats' => [
                'isLikedByMe' => $this->isLikedByMe,
                'isFavoritedByMe' => $this->isFavoritedByMe,
                'likeCount' => $this->likes_count,
                'commentCount' => $this->comments_count,
            ],
            'schedule_datetime' => $this->schedule_datetime,
        ];
    }
}
