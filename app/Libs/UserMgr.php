<?php
namespace App\Libs;

use Exception;
use Throwable;
use App\Models\User;

use App\Models\Timeline;
use Illuminate\Support\Str;

use App\Models\Notification;
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
//dd('toggleFollow.2', $attrs, $isFollowing);
                $timeline->receivePayment(
                    PaymentTypeEnum::SUBSCRIPTION,
                    $follower,
                    $timeline->user->price*100, // %FIXME: price should be on timeline not user
                    $customAttributes,
                );
            } else { // follow only
//dd('toggleFollow.3', $attrs, $isFollowing);
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
            Notification::create([
                'user_id' => $timeline->user->id,  // followee
                'timeline_id' => $timeline->id,
                'notified_by' => $follower->id,
                'description' => $description ?? '',
                'type' => $action
            ]);
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
