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
use Illuminate\Support\Facades\Mail;

use App\Notifications\IdentityVerificationRequestSent;
use App\Notifications\IdentityVerificationVerified;
use App\Notifications\IdentityVerificationRejected;

use App\Http\Resources\UserSetting as UserSettingResource;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\UserCollection;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\Fanledger;
use App\Models\Country;
use App\Models\Timeline;
use App\Rules\MatchOldPassword;
use App\Models\Diskmediafile;
use App\Models\Verifyrequest;
use App\Enums\MediafileTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Enums\VerifyStatusTypeEnum;
use App\Models\Staff;

class UsersController extends AppBaseController
{
    public function index(Request $request)
    {
        $query = User::query();
        // Apply filters %TODO
        $data = $query->paginate( $request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)) );
        return new UserCollection($data);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'firstname' => 'required|sometimes|string',
            'lastname' => 'required|sometimes|string',
            'email' => 'required|sometimes|email',
        ]);

        $user->fill($request->only([
            'firstname',
            'lastname',
            'email',
        ]));

        $user->save();

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
            'subscriptions.price_per_1_months' => 'numeric',
            'subscriptions.price_per_3_months' => 'numeric|nullable',
            'subscriptions.price_per_6_months' => 'numeric|nullable',
            'subscriptions.price_per_12_months' => 'numeric|nullable',
            'city' => 'string|min:2',
            'is_follow_for_free' => 'boolean',
            'blocked' => 'array',
            'message_with_tip_only' => 'boolean',
            'enable_message_with_tip_only_pay' => 'boolean',
            'about' => 'string|nullable',
            'country' => 'string|nullable',
            'city' => 'string|nullable',
            'gender' => 'in:male,female,other|nullable',
            'birthdate' => 'date|nullable',
            'weblinks' => 'array|nullable',
            'weblinks.*' => 'url|nullable',
            'body_type' => 'string|nullable',
            'chest' => 'string|nullable',
            'waist' => 'string|nullable',
            'hips' => 'string|nullable',
            'arms' => 'string|nullable',
            'hair_color' => 'string|nullable',
            'eye_color' => 'string|nullable',
            'age' => 'string|nullable',
            'height' => 'string|nullable',
            'weight' => 'string|nullable',
            'education' => 'string|nullable',
            'language' => 'string|nullable',
            'ethnicity' => 'string|nullable',
            'profession' => 'string|nullable',
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
    
            $cattrsFields = [ 'subscriptions', 'localization', 'weblinks', 'privacy', 'blocked', 'watermark', 'message_with_tip_only', 'enable_message_with_tip_only_pay' ];
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
            'resource_type'    => 'avatar',
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
            'resource_type'    => 'cover',
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

    // --- Identity Verification ---

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

    // Manually check status of a pending request
    // %TODO: put on queue (?)
    public function checkVerifyStatus(Request $request)
    {
        $sessionUser = $request->user();
        $user = $sessionUser;
        $vr = Verifyrequest::checkStatus($user);
        if ( $vr->vstatus ===  VerifyStatusTypeEnum::VERIFIED ) {
            $user->notify( new IdentityVerificationVerified($vr, $user) );
        } else if ( $vr->vstatus ===  VerifyStatusTypeEnum::REJECTED ) {
            $user->notify( new IdentityVerificationRejected($vr, $user) );
        }
        return response()->json( $vr );
    }

    // Add new staff account and send invitation email
    public function sendStaffInvite(Request $request)
    {
        $sessionUser = $request->user();
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string',
            'role' => 'required|string',
        ]);

        // Add new staff user
        $token = str_random(60);
        $email = $request->input('email');
        $users = User::where('email', $email)->get();

        Staff::create([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $email,
            'role' => $request->input('role'),
            'owner_id' => $sessionUser->id,
            'token' => $token,
            'creator_id' => $request->input('creator_id'),
        ]);

        // Send Inviation email
        $accept_link = 'http://localhost:8000/staff/invitations/accept?token='.$token.'&email='.$email.'&inviter='.$sessionUser->name.(count($users) == 0 ? '&is_new=true' : '');

        Mail::send('emails.staff_invite', ['user' => $sessionUser, 'accept_link' => $accept_link], function ($message) use(&$email)
        {
            $message->from('info@allfans.com', 'AllFans');

            $message->to($email)->subject('AllFans Staff Invitation');
        });

        return response()->json( ['status' => 200] );
    }
}
