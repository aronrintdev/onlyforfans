<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Contactflag as ContactflagModel;

class Contactflag extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();
        $model = ContactflagModel::find($this->id);
        $hasAccess = $sessionUser->can('view', $model);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'commentable_id' => $this->commentable_id,
            'commentable_type' => $this->commentable_type,
            'parent_id' => $this->parent_id,
            'user_id' => $this->user_id,
            'description' =>  $this->when($hasAccess, $this->description),
            //'mediafiles' =>  $this->when($hasAccess, $this->mediafiles),
            'created_at' => $this->created_at,
        ];
    }
}
