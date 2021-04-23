<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Favorite as FavoriteModel;
use App\Http\Resources\Mediafile as MediafileResource;
use App\Http\Resources\Post as PostResource;

class Favorite extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();
        $model = FavoriteModel::find($this->id);
        //$hasAccess = $sessionUser->can('view', $model);

        switch($this->favoritable_type) {
        case 'mediafiles':
            $fobj = new MediafileResource($this->favoritable);
            break;
        case 'posts':
            $fobj = new PostResource($this->favoritable->load('mediafiles'));
            break;
        default:
            $fobj = $this->favoritable;
        }
        return [
            'id' => $this->id,
            'favoritable_id' => $this->favoritable_id,
            'favoritable_type' => $this->favoritable_type,
            'favoritable' => $fobj, // $this->favoritable,
            'creator' => $this->favoritable->user,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
        ];
    }
}
