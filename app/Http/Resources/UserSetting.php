<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\UserSetting as UserSettingModel;

class UserSetting extends JsonResource
{
    public function toArray($request)
    {
        //$sessionUser = $request->user();
        $userSetting = UserSettingModel::find($this->id); // %FIXME: n+1 performance issue (not so bad if paginated?)
        $timeline = $userSetting->user->timeline;

        $response =  parent::toArray($request);
        $response['is_follow_for_free'] = $timeline->is_follow_for_free;
        return $response;
    }
}
