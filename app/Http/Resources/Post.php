<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\PostTypeEnum;

class Post extends JsonResource
{
    public function toArray($request)
    {
        //dd('here', $request->user());
        //dd('here', $this);
        //return parent::toArray($request);

        $sessionUser = $request->user();

        switch ($this->type) {
        case PostTypeEnum::FREE:
            $hasAccess = true;
            break;
        case PostTypeEnum::PRICED:
            //$hasAccess = $sessionUser->purchasedposts->contains($this
            $hasAccess = $this->sharees->contains($sessionUser->id);
            break;
        case PostTypeEnum::SUBSCRIBER:
            $hasAccess = $this->timeline->subscribers->contains($sessionUser->id);
            break;
        }
$hasAccess = false; // %FIXME HERE MONDAY

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
