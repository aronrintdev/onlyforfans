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
use App\Models\User;
use App\Models\Fanledger;
use App\Models\Country;
use App\Enums\PaymentTypeEnum;
use App\Rules\MatchOldPassword;

class UsersController extends AppBaseController
{
    public function index(Request $request)
    {
        $query = User::query();

        // Apply filters
        //  ~ %TODO

        return response()->json([
            'users' => $query->get(),
        ]);
    }

    public function showSettings(Request $request, User $user)
    {
        $this->authorize('show', $user);
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

    public function updateSettings(Request $request, User $user)
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
    
            $cattrsFields = [ 'subscriptions', 'localization', 'weblinks', 'privacy', 'blocked', 'watermark', ];
            $attrs = $request->except($cattrsFields);

            $userSetting = $user->settings;
            $userSetting->fill($attrs);

            // handle cattrs
            if ($request->hasAny($cattrsFields) ){
                $cattrs = $userSetting->cattrs; // 'pop'
                foreach ($cattrsFields as $k) {
                    switch ($k) {
                    case 'blocked': // %FIXME: move to lib
                        if ( $request->has('blocked') ) {
                            $byCountry = [];
                            $byIP = [];
                            $byUsername = [];
                            foreach ( $request->blocked as $bobj) {
                                $slug = trim($bobj['slug'] ?? '');
                                $text = trim($bobj['text'] ?? '');
                                do {
                                    // country
                                    $exists = Country::where('slug', $slug)->first();
                                    if ( $exists ) { 
                                        $byCountry[] = $slug;
                                        break;
                                    }
                                    // user
                                    $exists = User::where('username', $slug)->first();
                                    if ( $exists ) {
                                        $byUsername[] = $slug;
                                        break;
                                    }
                                    // IP
                                    if ( filter_var($text, FILTER_VALIDATE_IP) ) { // ip
                                        $byIP[] = $text;
                                        break;
                                    }
                                } while(0);
                            }
                            $blocked = $cattrs['blocked'] ?? [];
                            $blocked['ips'] = $blocked['ips'] ?? [];
                            $blocked['countries'] = $blocked['countries'] ?? [];
                            $blocked['usernames'] = $blocked['usernames'] ?? [];
                            array_push($blocked['ips'], ...$byIP);
                            array_push($blocked['countries'], ...$byCountry);
                            array_push($blocked['usernames'], ...$byUsername);
                            $cattrs['blocked'] = array_map('array_unique', $blocked);
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
        $sessionUser = Auth::user(); // sender of tip
        $sessionUser->makeVisible('email');

        $sales = Fanledger::where('seller_id', $sessionUser->id)->sum('total_amount');

        $timeline = $sessionUser->timeline;
        $timeline->userstats = [ // %FIXME DRY
            'post_count'       => $timeline->posts->count(),
            'like_count'       => $timeline->user->likedposts->count(),
            'follower_count'   => $timeline->followers->count(),
            'following_count'  => $timeline->user->followedtimelines->count(),
            'subscribed_count' => 0, // %TODO $sessionUser->timeline->subscribed->count()
            'earnings'         => $sales,
        ];

        /** Flags for the common UI elements */
        $uiFlags = [
            'isAdmin'          => $sessionUser->can('admin.dashboard'),
            'isCreator'        => $sessionUser->settings->is_creator ?? false,
            'hasBanking'       => false, // TODO: Add Logic when banking is setup
            'hasEarnings'      => $sales > 0,
            'hasPaymentMethod' => false, // TODO: Add Logic when payment method is setup
        ];

        return [
            'session_user' => $sessionUser,
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

    public function match(Request $request)
    {
        if ( !$request->ajax() ) {
            \App::abort(400, 'Requires AJAX');
        }

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
}
