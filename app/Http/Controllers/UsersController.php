<?php
namespace App\Http\Controllers;

use App;
use Auth;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Mail;

use App\Notifications\IdentityVerificationRequestSent;
use App\Notifications\IdentityVerificationVerified;
use App\Notifications\IdentityVerificationRejected;
use App\Notifications\PasswordChanged;

use App\Http\Resources\UserSetting as UserSettingResource;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\UserCollection;

use App\Models\Staff;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\Timeline;
use App\Rules\MatchOldPassword;
use App\Models\Diskmediafile;
use App\Models\Verifyrequest;
use App\Enums\MediafileTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Enums\VerifyStatusTypeEnum;
use App\Apis\Sendgrid\Api as SendgridApi;
use Money\Currency;
use Money\Money;

class UsersController extends AppBaseController
{
    public function index(Request $request)
    {
        $query = User::query();
        // Apply filters %TODO
        $data = $query->paginate( $request->input('take', Config::get('collections.defaultMax', 10)) );
        return new UserCollection($data);
    }

    public function show(Request $request, User $user)
    {
        $this->authorize('view', $user);
        if ($request->user()->isAdmin()) {
            $user->load('settings');
            return [ 'data' => $user ];
        }

        return new UserResource($user);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'username' => 'required|sometimes|string',
            'email' => 'required|sometimes|email',
            'slug' => 'required|sometimes|string',
        ]);

        $timeline = $user->timeline;

        if ( $request->has('email') ) {
            $user->email = $request->email;
            $user->save();
        }

        if ( $request->has('slug') ) {
            $timeline->fill( $request->only([ 'slug' ]) );
            $timeline->save();
        }

        return new UserResource($user);
    }

    public function showSettings(Request $request, User $user)
    {
        $this->authorize('view', $user);
        $staff = Staff::where('user_id', $user->id)->where('role', 'manager')->get();
        $user->settings['is_manager'] = count($staff) > 0;
        return new UserSettingResource($user->settings);
    }

    // for the user updating their password while logged in
    public function updatePassword(Request $request, User $user=null)
    {
        $this->authorize('update', $user); // %FIXME: should be update password?
        $request->validate([
            'oldPassword' => ['required', new MatchOldPassword],
            'newPassword' => 'required|min:' . Config::get('auth.passwords.user.minLength'),
            //'newPassword' => 'required|confirmed|min:8',
            //'newPasswordConfirm' => 'same:newPassword',
        ]);
        $sessionUser = $user ?? $request->user(); // $user param for reset by super-admin, TBD
        $sessionUser->password = Hash::make($request->newPassword);
        $sessionUser->save();

        $sessionUser->notify(new PasswordChanged($sessionUser));
        //event(new PasswordReset($sessionUser));

        return response()->json([ ]);
    }

    public function enableSetting(Request $request, User $user, string $group)  // single
    {
        $this->authorize('update', $user);
        $vrules = UserSetting::$vrules[$group];
        $request->validate($vrules);
        //dd($request->all());

        $userSetting = $user->settings;

        $result = $userSetting->enable($group, $request->all() );
        //dd($request->all(), $result);

        $userSetting->refresh();
        return $userSetting;
    }

    public function disableSetting(Request $request, User $user, string $group)  // single
    {
        $this->authorize('update', $user);
        $vrules = UserSetting::$vrules[$group];
        $request->validate($vrules);
        $userSetting = $user->settings;
        $result = $userSetting->disable($group, $request->all() );
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
            'name'                             => 'string',
            'city'                             => 'string|min:2|nullable',
            'blocked'                          => 'array',
            'message_with_tip_only'            => 'boolean',
            'enable_message_with_tip_only_pay' => 'boolean',
            'about'            => 'string|nullable', // goes to timeline
            'country'          => 'string|nullable',
            'gender'           => 'in:male,female,other|nullable',
            'birthdate'        => 'date|nullable',
            'weblinks'         => 'array|nullable',
            'weblinks.*'       => 'string|nullable',
            'weblinks.website' => 'domain|nullable',
            'body_type'        => 'string|nullable',
            'chest'            => 'string|nullable',
            'waist'            => 'string|nullable',
            'hips'             => 'string|nullable',
            'arms'             => 'string|nullable',
            'hair_color'       => 'string|nullable',
            'eye_color'        => 'string|nullable',
            'age'              => 'string|nullable',
            'height'           => 'string|nullable',
            'weight'           => 'string|nullable',
            'education'        => 'string|nullable',
            'language'         => 'string|nullable',
            'ethnicity'        => 'string|nullable',
            'profession'       => 'string|nullable',
        ]);
        $request->request->remove('username'); // disallow username updates for now

        $userSetting = DB::transaction(function () use(&$user, &$request) {

            // handle fields that reside in [timelines]
            $timeline = $user->timeline;
            if ($request->has('about')) {
                $timeline->about = $request->about;
            }
            if ($request->has('name')) {
                $timeline->fill($request->only([
                    'name',
                ]));
            }
            $timeline->save();


            $cattrsFields = [
                'blocked',
                'enable_message_with_tip_only_pay',
                'localization',
                'message_with_tip_only',
                'privacy',
                'watermark',
            ];
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

    public function updateAvatar(Request $request)
    {
        $sessionUser = $request->user();
        $file = $request->file('avatar');

        if (!$file)
        {
            abort(400);
        }

        $subFolder = $sessionUser->id;
        $s3Path = $file->store($subFolder, 's3');

        $mediafile = Diskmediafile::doCreate([
            'owner_id'         => $sessionUser->id,
            'filepath'         => $s3Path,
            'mimetype'         => $file->getMimeType(),
            'orig_filename'    => $file->getClientOriginalName(),
            'orig_ext'         => $file->getClientOriginalExtension(),
            'mfname'           => $file->getClientOriginalName(),
            'mftype'           => MediafileTypeEnum::AVATAR,
            'resource_id'      => $sessionUser->id,
            'resource_type'    => 'users',
        ]);

        $sessionUser->timeline->avatar_id = $mediafile->id;
        $sessionUser->timeline->save();

        return response()->json([
            'avatar' => $mediafile
        ]);
    }

    public function updateCover(Request $request)
    {
        $sessionUser = $request->user();
        $file = $request->file('cover');

        if (!$file)
        {
            abort(400);
        }

        $subFolder = $sessionUser->id;
        $s3Path = $file->store($subFolder, 's3');

        $mediafile = Diskmediafile::doCreate([
            'owner_id'         => $sessionUser->id,
            'filepath'         => $s3Path,
            'mimetype'         => $file->getMimeType(),
            'orig_filename'    => $file->getClientOriginalName(),
            'orig_ext'         => $file->getClientOriginalExtension(),
            'mfname'           => $file->getClientOriginalName(),
            'mftype'           => MediafileTypeEnum::COVER,
            'resource_id'      => $sessionUser->id,
            'resource_type'    => 'users',
        ]);

        $sessionUser->timeline->cover_id = $mediafile->id;
        $sessionUser->timeline->save();

        return response()->json([
            'cover' => $mediafile
        ]);
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
        $timeline->is_storyqueue_empty = $timeline->isStoryqueueEmpty();

        // get companies
        $models = Staff::with('permissions')->where('user_id', $sessionUser->id)->get();
        $companies = [];
        foreach( $models as $model) {
            if ($model->role == 'staff') {
                $user = Timeline::with(['cover', 'avatar'])->where('user_id', $model->creator_id)->firstOrFail()->makeVisible(['user']);
                $user->permissions = $model->permissions;
                array_push($companies, $user);
            } else if ($model->role == 'manager') {
                $user = Timeline::with(['cover', 'avatar'])->where('user_id', $model->owner_id)->firstOrFail()->makeVisible(['user']);
                array_push($companies, $user);
            }
        }
        $sessionUser->companies = $companies;

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

    // %FIXME: should this be in timeline controller instead (??)
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

        //return \Response::json([ 'collection'=> $collection, ]);
        $field = $request->has('field') ? $request->field : null;

        if ($field == 'slug') {
            $collection = Timeline::where( function($q1) use($term) {
                //$q1->where('first_name', 'like', $term.'%')->orWhere('last_name', 'like', $term.'%');
                $q1->where('slug', 'like', $term.'%');
             })
             //->where('estatus', EmployeeStatusEnum::ACTIVE) // active users only
             ->get();
             return response()->json( $collection->map( function($item,$key) {
                $attrs = [
                    'id' => $item->id,
                    'label' => $item->slug,
                ];
                return $attrs;
             }) );
        }

        $collection = User::where( function($q1) use($term) {
            //$q1->where('first_name', 'like', $term.'%')->orWhere('last_name', 'like', $term.'%');
            $q1->where('email', 'like', $term.'%');
         })
         //->where('estatus', EmployeeStatusEnum::ACTIVE) // active users only
         ->get();

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

    // --- Identity Verification ---
    // %TODO move to own controller

    // Send a request for identity verification (starts the process)
    public function requestVerify(Request $request)
    {
        // %FIXME: make sure the name they type in matches the name in our database?
        // %FIXME: try - catch
        // %TODO: put on queue (?)

        $sessionUser = $request->user();
        $user = $sessionUser;

        $request->validate([
            'mobile' => 'required|digits:10', // assume US (w/o country code) for now & Vue client strips out extra chars
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'hasAllowedNSFW' => 'required|bool',
            //'country' => 'required|string|size:2',
            //'dob' => 'required|date',
        ]);

        $attrs = $request->except(['mobile']);
        $attrs['mobile'] = '+1'.$request->mobile; // append US country code (required by ID Merit) %FIXME hardcoded to US
        $vr = Verifyrequest::verifyUser($user, $attrs);

        // Store real name (keep in mind not yet verified!)
        $user->real_firstname = $request->firstname;
        $user->real_lastname = $request->lastname;
        $user->save();

        $updateSettings['has_allowed_nsfw'] = $request->hasAllowedNSFW;
        $user->settings->update($updateSettings);

        $user->notify( new IdentityVerificationRequestSent($vr, $user) );

        return response()->json( $vr );
    }

    // Manually check status of a pending request for the session user
    // %TODO: put on queue (?)
    public function checkVerifyStatus(Request $request)
    {
        $sessionUser = $request->user();
        $user = $sessionUser;

        // only submit if there's an existing pending request
        $userId = $user->id;
        $pending = Verifyrequest::where('requester_id', $userId)
            ->where('vstatus', VerifyStatusTypeEnum::PENDING)
            ->orderBy('created_at', 'desc')
            ->get();
        if ( !$pending->count() ) {
            throw new Exception('UserController::checkVerifyStatus() - User has no verification request currently pending');
        }
        $vr = $pending[0]; // use latest pending request for this user

        $vr = Verifyrequest::checkStatusByGUID($vr->guid);

        if ( $vr->vstatus ===  VerifyStatusTypeEnum::VERIFIED ) {
            $user->notify( new IdentityVerificationVerified($vr, $user) );
        } else if ( $vr->vstatus ===  VerifyStatusTypeEnum::REJECTED ) {
            $user->notify( new IdentityVerificationRejected($vr, $user) );
        }
        return response()->json( $vr );
    }

    // Check user's referral code and generate code if user has no it
    public function checkReferralCode(Request $request) {
        $sessionUser = $request->user();
        if (empty($sessionUser->referral_code)) {
            // Generate referral_code for new user
            do {
                $referral_code = mt_rand( 00000000, 99999999 );
            } while (User::where('referral_code', '=', str_pad($referral_code, 8 , '0' , STR_PAD_LEFT))->exists());
            $referral_code = str_pad($referral_code, 8 , '0' , STR_PAD_LEFT);
            $updateUser['referral_code'] = $referral_code;
            $sessionUser->update($updateUser);
        } else {
            $referral_code = $sessionUser->referral_code;
        }
        return response()->json(
            ['referralCode' => $referral_code]
        );
    }

    public function loginAsUser(Request $request, User $user)
    {
        $sessionUser = $request->user();
        if ( !$sessionUser->isAdmin() ) {
            abort(404);
        }
        Auth::loginUsingId($user->id);
        return response()->json([]);
    }
}
