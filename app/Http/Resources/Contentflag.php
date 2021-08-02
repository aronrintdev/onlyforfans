<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Contentflag as ContentflagModel;

class Contentflag extends JsonResource
{
    public function toArray($request)
    {
        //$sessionUser = $request->user();
        //$model = ContentflagModel::find($this->id);
        //$hasAccess = $sessionUser->can('view', $model);

        return [
            'id' => $this->id,
            'cfstatus' => $this->cfstatus,
            'flaggable_type' => $this->flaggable_type,
            'flaggable_id' => $this->flaggable_id,
            'notes' => $this->notes,
            'cattrs' => $this->cattrs,
            'created_at' => $this->created_at,
        ];
    }
}
