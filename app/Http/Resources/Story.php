<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Story as StoryModel;

class Story extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();
        $model = StoryModel::find($this->id);
        $hasAccess = $sessionUser->can('view', $model);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'stype' => $this->stype,
            'timeline_id' => $this->timeline_id,
            'content' =>  $this->when($hasAccess, $this->content),
            'mediafiles' =>  $this->when($hasAccess, $this->mediafiles),
            'created_at' => $this->created_at,
        ];
    }
}
