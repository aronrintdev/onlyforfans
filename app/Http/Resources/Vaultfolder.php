<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Vaultfolder as VaultfolderModel;

class Vaultfolder extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();
        $model = VaultfolderModel::find($this->id);
        $hasAccess = $sessionUser->can('view', $model);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'vfname' => $this->vfname,
            'parent_id' => $this->parent_id,
            'vault_id' => $this->vault_id,
            'mediafiles' =>  $this->when($hasAccess, $this->mediafiles),
            //'sharees' =>  $this->sharees,
            //'mediafilesharelogs' =>  $this->mediafilesharelogs,
            'mediafile_count' =>  $this->mediafile_count,
            'vfchildren_count' =>  $this->vfchildren_count,
            'created_at' => $this->created_at,
        ];
    }
}
