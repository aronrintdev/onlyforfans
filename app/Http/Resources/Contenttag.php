<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Contenttag as ContenttagModel;

class Contenttag extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'ctag' => $this->ctag,
            'mediafiles_count' => $this->mediafiles_count,
            'posts_count' => $this->posts_count,
            'vaultfolders_count' => $this->vaultfolders_count,
            'created_at' => $this->created_at,
        ];
    }
}
