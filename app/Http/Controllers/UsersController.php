<?php
namespace App\Http\Controllers;

use App;
use Auth;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Http\Resources\UserSetting as UserSettingResource;
use App\Http\Resources\UserCollection;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\Fanledger;
use App\Models\Country;
use App\Models\Timeline;
use App\Enums\PaymentTypeEnum;
use App\Rules\MatchOldPassword;

class UsersController extends AppBaseController
{
    public function index(Request $request)
    {
        $query = User::query();
        // Apply filters %TODO
        $data = $query->paginate( $request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)) );
        return new UserCollection($data);
    }

    public function showSettings(Request $request, User $user)
    {
        $this->authorize('view', $user);
        return new UserSettingResource($user->settings);
    }

    // for the user updating their password while logged in
    public function updatePassword(Request $request, User $user=null)
    {
        $this->authorize('update', $user); // %FIXME: should be update password?
        $request->validate([
            'oldPassword' => ['required', new MatchOldPassword],
            'newPassword' => 'required|min:'.env('MIN_PASSWORD_CHAR_LENGTH', 8),
            //'newPassword' => 'required|confirmed|min:8',
            //'newPasswordConfirm' => 'same:newPassword',
        ]);
        $sessionUser = $user ?? $request->user(); // $user param for reset by super-admin, TBD
        $sessionUser->password = Hash::make($request->newPassword);
        $sessionUser->save();
        //event(new PasswordReset($sessionUser));
        return response()->json([ ]);
    }

    public function enableSetting(Request $request, User $user, string $group)  // single
    {
        $this->authorize('update', $user);
        switch ($group) {
        case 'notifications':
            $vrules = [
                'global.*' => 'array',
                'global.*.*' => 'string|in:email,sms,site,push',
                'campaigns.*' => 'array',
                'campaigns.*.*' => 'string|in:email,sms,site,push',
                'refunds.*' => 'array',
                'refunds.*.*' => 'string|in:email,sms,site,push',
                'income.*' => 'array',
                'income.*.*' => 'string|in:email,sms,site,push',
                'posts.*' => 'array',
                'posts.*.*' => 'string|in:email,sms,site,push',
            ];
            break;
        }
        $request->validate($vrules);
        //dd($request->all());

        $userSetting = $user->settings;

        $result = $userSetting->enable($group, $request->except(['foo']) );
        //dd($request->all(), $result);

        $userSetting->refresh();
        return $userSetting;
    }

    public function disableSetting(Request $request, User $user, string $group)  // single
    {
        $this->authorize('update', $user);
        switch ($group) {
        case 'notifications':
            $vrules = [
                'global.*' => 'array',
                'global.*.*' => 'string|in:email,sms,site,push',
                'campaigns.*' => 'array',
                'campaigns.*.*' => 'string|in:email,sms,site,push',
                'refunds.*' => 'array',
                'refunds.*.*' => 'string|in:email,sms,site,push',
                'income.*' => 'array',
                'income.*.*' => 'string|in:email,sms,site,push',
                'posts.*' => 'array',
                'posts.*.*' => 'string|in:email,sms,site,push',
            ];
            break;
        }
        $request->validate($vrules);
        $userSetting = $user->settings;
        $result = $userSetting->disable($group, $request->except(['foo']) );
        $userSetting->refresh();
        return $userSetting;
    }

    // %NOTE: this updates settings in 'batches'...so the request payload must contain all keys for a group such as 'privacy', 
    // even if only one is actually changing
    // %NOTE: in Users controller as the request param passed is User type
    public function updateSettingsBatch(Request $request, User $user) // batch
    {
        $this->authorize('update', $user);
        $request->validate([
            'city' => 'string|min:2',
            'is_follow_for_free' => 'boolean',
            'blocked' => 'array',
        ]);
        $request->request->remove('username'); // disallow username updates for now

        $userSetting = DB::transaction(function () use(&$user, &$request) {

            $timeline = $user->timeline;

            // %TODO %FIXME: subscriptions should be in [timelines].cattrs, not user settings

            // handle fields that reside in [timelines]
            if ( $request->has('is_follow_for_free') ) {
                $timeline->is_follow_for_free = $request->boolean('is_follow_for_free');
                $timeline->save();
                $request->request->remove('is_follow_for_free');
            }
    
            $cattrsFields = [ 'notifications', 'subscriptions', 'localization', 'weblinks', 'privacy', 'blocked', 'watermark', ];
            $attrs = $request->except($cattrsFields);

            $userSetting = $user->settings;
            $userSetting->fill($attrs);

            // handle cattrs
            if ($request->hasAny($cattrsFields) ) {
                $cattrs = $userSetting->cattrs; // 'pop'
                foreach ($cattrsFields as $k) {
                    switch ($k) {
                    case 'blocked': // %FIXME: move to lib
                        if ( $request->has('blocked') ) {
                            $cattrs['blocked'] = UserSetting::parseBlockedBatched($request->blocked, $cattrs['blocked']);
                        }
                        break;
                    default:
                        $cattrs[$k] = $request->has($k) ? $request->input($k) : ($cattrs[$k]??null); // take from request (overwrite current value), else keep current value
                    }
                }
                $userSetting->cattrs = $cattrs; // 'push'
            }
    
            $userSetting->save();
            return $userSetting;
        });
    
        return new UserSettingResource($userSetting);
    }

    public function me(Request $request)
    {
        $sessionUser = $request->user(); // sender of tip
        $sessionUser->makeVisible('email');
        //$timeline = $sessionUser->timeline->with([]);
        //$timeline = $sessionUser->timeline;
        //$timeline = Timeline::with(['followers', 'following',])
        $timeline = Timeline::with(['avatar', 'cover'])
            ->where('user_id', $sessionUser->id)
            ->first();

        $timeline->userstats = $sessionUser->getStats();

        /** Flags for the common UI elements */
        $uiFlags = [
            'isAdmin'          => $sessionUser->can('admin.dashboard'),
            'isCreator'        => $sessionUser->settings->is_creator ?? false,
            'hasBanking'       => false, // TODO: Add Logic when banking is setup
            'hasEarnings'      => $sessionUser->hasEarnings(),
            'hasPaymentMethod' => false, // TODO: Add Logic when payment method is setup
        ];

        return [
            'session_user' => $sessionUser->setHidden(['timeline', 'settings']),
            'timeline'     => $timeline,
            'uiFlags'      => $uiFlags,
        ];
    }

    public function tip(Request $request, $id)
    {
        $sessionUser = Auth::user(); // sender of tip
        try {
            $tippee = User::findOrFail($id);
            $tippee->receivePayment(
                PaymentTypeEnum::TIP,
                $sessionUser,
                $request->amount,
                [ 'notes' => $request->note ?? '' ]
            );

        } catch(Exception | Throwable $e){
            Log::error(json_encode([
                'msg' => 'UsersController::tip() - error',
                'emsg' => $e->getMessage(),
            ]));
            //throw $e;
            return response()->json(['status'=>'400', 'message'=>$e->getMessage()]);
        }

        return response()->json([
            'tippee' => $tippee ?? null,
        ]);
    }

    // currently goes by email
    public function match(Request $request)
    {
        $term = $request->input('term',null);
        if ( empty($term) ) {
            return [];
        }

        $collection = User::where( function($q1) use($term) {
                         //$q1->where('first_name', 'like', $term.'%')->orWhere('last_name', 'like', $term.'%');
                         $q1->where('email', 'like', $term.'%');
                      })
                      //->where('estatus', EmployeeStatusEnum::ACTIVE) // active users only
                      ->get();

        //return \Response::json([ 'collection'=> $collection, ]);
        $field = $request->has('field') ? $request->field : null;

        return response()->json( $collection->map( function($item,$key) use($field) {
            $attrs = [
                    'id' => $item->id,
                    'value' => $item->id,
                    'label' => $field ? $item->{$field} : $item->email,
                    //'value' => $field ? $item->{$field} : $item->slug, // default to username/slug
                    //'label' => $item->renderName(),
                ];
                return $attrs;
        }) );

    }
    public function updateLastSeen(Request $request) {
        $sessionUser = $request -> user();
        $status = $request -> input('status');
        if ($status) {
            $sessionUser->is_online = true;
            $sessionUser->last_logged = null;
        } else {
            $sessionUser->last_logged = new \DateTime();
            $sessionUser->is_online = false;
        }
        $sessionUser->save();
        return ['status' => 200];
    }
}
