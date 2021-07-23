<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Mycontact as MycontactModel;

class Mycontact extends JsonResource
{
    public function toArray($request)
    {
        //$sessionUser = $request->user();
        //$model = MycontactModel::find($this->id);
        //$hasAccess = $sessionUser->can('view', $model);

        return [
            'id' => $this->id,
            'contact_id' => $this->contact_id,
            'contact' => $this->contact,
            'owner_id' => $this->owner_id,
            'alias' => $this->alias,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            //'cattrs' => $this->cattrs,
        ];
    }
}
