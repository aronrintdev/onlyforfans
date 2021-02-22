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
        $post = PostModel::find($this->id); // %FIXME: n+1 performance issue
        $hasAccess = $sessionUser->can('view', $post);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'type' => $this->type,
            //'description' => $hasAccess ? $this->description : null,
            'description' =>  $this->when($hasAccess, $this->description),
            'created_at' => $this->created_at,
        ];
    }
}
