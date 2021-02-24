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
            'postable_id' => $this->postable_id,
            'postable_type' => $this->postable_type,
            'description' =>  $this->when($hasAccess, $this->description),
            'mediafiles' =>  $this->when($hasAccess, $this->mediafiles),
            // https://laravel.com/docs/8.x/eloquent-resources#conditional-relationships
            //'mediafiles' =>  $this->when( $hasAccess, MediafileResource::collection($this->whenLoaded('mediafiles')) ), // ??
            'created_at' => $this->created_at,
        ];
    }
}
