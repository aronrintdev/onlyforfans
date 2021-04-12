<?php
namespace App\Libs;

use Exception;
use Throwable;
use App\Models\User;

use App\Models\Timeline;
use Illuminate\Support\Str;

use App\Enums\PaymentTypeEnum;
use Illuminate\Support\Collection; 
use Illuminate\Support\Facades\Log;

class UserMgr {

    public static function toggleFollow(User $follower, Timeline $timeline, array $attrs = []) : ?array
    {
        throw new Exception('deprecated');

        //if ( !checkBlockedProfiles($timeline->username) ){
            //throw new Exception('Unable to subscribe, error 159');
        //}

        $isFollowing = $timeline->followers->contains($follower->id);

        $customAttributes = [];
        if ( array_key_exists('referer', $attrs) && $attrs['referer'] ) {
            $customAttributes['referer'] = $referer;
        }

        if ( $isFollowing ) { // unfollow
            $action = 'unfollow';
            $follower->followedtimelines()->detach($timeline->id);
        } else { // follow
            $action = 'follow';
            if ($attrs['is_subscribe']) {
                $timeline->receivePayment(
                    PaymentTypeEnum::SUBSCRIPTION,
                    $follower,
                    $timeline->price,
                    $customAttributes,
                );
            } else { // follow only
                $follower->followedtimelines()->attach($timeline->id, [
                    'cattrs' => json_encode($customAttributes),
                ]);
                //$timeline->followers()->attach($follower->id, [ 'cattrs' => $customAttributes ]);
            }
        }


        //$follow = User::where('timeline_id', '=', $timeline_id)->first();

        try {
            switch ($action) {
            case 'follow':
                $description = $follower->name.' '.trans('common.is_following_you');
                $message = 'successfully followed';
                break;
            case 'unfollow':
                $description = $follower->name.' '.trans('common.is_unfollowing_you');
                $message = 'successfully un-followed';
                break;
            }
        } catch(Exception | Throwable $e) {
            throw $e;
            Log::error(json_encode([
                'msg' => 'UserMgr::follow() - Could not send notification',
                'emsg' => $e->getMessage(),
            ]));
        }

        return [
            'action' => $action,
            'message' => $message,
        ];
    }

}
