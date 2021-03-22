<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Bookmark as BookmarkModel;

class Bookmark extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();
        $model = BookmarkModel::find($this->id);
        $hasAccess = $sessionUser->can('view', $model);

        return [
            'id' => $this->id,
            'bookmarkable_id' => $this->bookmarkable_id,
            'bookmarkable_type' => $this->bookmarkable_type,
            'bookmarkable' => $this->bookmarkable,
            'creator' => $this->bookmarkable->user,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
        ];
    }
}
