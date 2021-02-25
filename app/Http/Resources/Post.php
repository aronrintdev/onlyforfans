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
            'postable_id' => $this->postable_id,
            'postable_type' => $this->postable_type,
            'description' =>  $this->when($hasAccess, $this->description),
            'mediafiles' =>  $this->when($hasAccess, $this->mediafiles),
            'user' =>  $this->when($hasAccess, $this->user),
            // https://laravel.com/docs/8.x/eloquent-resources#conditional-relationships
            //'mediafiles' =>  $this->when( $hasAccess, MediafileResource::collection($this->whenLoaded('mediafiles')) ), // ??
            'created_at' => $this->created_at,
        ];
    }
}
