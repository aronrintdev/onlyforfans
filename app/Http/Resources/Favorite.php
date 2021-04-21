<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Favorite as FavoriteModel;

class Favorite extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();
        $model = FavoriteModel::find($this->id);
        $hasAccess = $sessionUser->can('view', $model);

        return [
            'id' => $this->id,
            'favoritable_id' => $this->favoritable_id,
            'favoritable_type' => $this->favoritable_type,
            'favoritable' => $this->favoritable,
            'creator' => $this->favoritable->user,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
        ];
    }
}
