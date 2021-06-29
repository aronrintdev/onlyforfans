<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Mycontact as MycontactModel;
use Carbon\Carbon;

/**
 * Converts User Object to Mycontact for session user or temp Mycontact for the messaging filters
 * @package App\Http\Resources
 */
class MycontactUser extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();

        // Attempt to get Mycontact for this user
        $mycontact = MycontactModel::where('owner_id', $sessionUser->id)->where('contact_id', $this->id)->first();
        //$hasAccess = $sessionUser->can('view', $model);

        \Log::debug('MycontactUser Resource', [
            '$sessionUser' => $sessionUser->id,
            'user' => $this->id,
            'mycontact' => isset($mycontact) ? $mycontact->id : null,
        ]);

        if (isset($mycontact)) {
            return [
                'id' => $mycontact->id,
                'contact_id' => $mycontact->contact_id,
                'contact' => new Timeline($this->timeline),
                'owner_id' => $mycontact->owner_id,
                'alias' => $mycontact->alias,
                'created_at' => $mycontact->created_at,
                'updated_at' => $mycontact->updated_at,
                'temp' => false,
                //'cattrs' => $this->cattrs,
            ];
        }

        // If mycontact does not exist
        return [
            'id' => $this->id,
            'contact_id' => $this->id,
            'contact' => new Timeline($this->timeline),
            'owner_id' => $sessionUser->id,
            'alias' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'temp' => true,
            //'cattrs' => $this->cattrs,
        ];
    }
}
