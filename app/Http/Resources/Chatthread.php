<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Chatthread as ChatthreadModel;

class Chatthread extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();
        $model = ChatthreadModel::find($this->id);
        $hasAccess = $sessionUser->can('view', $model);

        return [
            'id' => $this->id,
            'originator_id' => $this->originator_id,
            'is_tip_required' => $this->is_tip_required,
            'created_at' => $this->created_at,
        ];
    }
}
