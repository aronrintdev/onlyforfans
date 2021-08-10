<?php
namespace App\Http\Controllers\Auth;

//use DB;

use Validator;
use App\Models\User;
use App\Models\Token;
use App\Models\Invite;
use App\Models\Setting;
use App\Models\Referral;

use Illuminate\Http\Request;
use App\Models\Diskmediafile;
use Illuminate\Support\Carbon;
use App\Enums\MediafileTypeEnum;
use App\Notifications\VerifyEmail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }


    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     *
     * @param null $captcha
     * @param bool $socialLogin
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data, $captcha = null, $socialLogin = false)
    {
        $messages = [
            'no_admin'     => trans('validation.no_admin'),
            'tos.required' => trans('messages.tos_required'),
        ];
        $rules = [
            'email'        => 'required|email|max:255|unique:users,email',
            // 'name'         => 'max:255',
            'name'         => [ 'required', 'max:25', 'min:2', 'no_admin', new \App\Rules\ValidUsername ],
            'username'     => [ 'max:25', 'min:5', 'unique:users', 'no_admin', new \App\Rules\ValidUsername ],
            'password'     => 'required|min:6',
            // 'affiliate'    => 'exists:timelines,username',
        ];

        if (!$socialLogin) {
            $rules = array_merge($rules, [
                'tos' => 'required',
            ]);
        }

        if ($captcha) {
            $rules['g-recaptcha-response'] = 'required|recaptchav3:register,0.5';
        }

        if (Config::get('auth.beta.active')) {
            $rules['token'] = [ 'required', new \App\Rules\ValidBetaToken ];
        }

        return Validator::make($data, $rules, $messages);
    }

    public function register(Request $request)
    {
        return $this->registerUser($request);
    }

    protected function registerUser(Request $request)
    {

        // %FIXME %TODO: use transaction

        $validator = $this->validator($request->all(), true, false);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['err_result' => $validator->errors()->toArray()]);
            }
            return false;
        }

        if(Setting::get('mail_verification') === 'off') {
            $mail_verification = 1;
        } else {
            $mail_verification = 0;
        }

        // Generate referral_code for new user
        do {
            $referral_code = mt_rand( 00000000, 99999999 );
        } while (User::where('referral_code', '=', str_pad($referral_code, 8 , '0' , STR_PAD_LEFT))->exists());
        $referral_code = str_pad($referral_code, 8 , '0' , STR_PAD_LEFT);

        //Create user record
        $user = User::create([
            'email'             => $request->email,
            'password'          => bcrypt($request->password),
            'verification_code' => str_random(30),
            'username'          => $request->username,
            'remember_token'    => str_random(10),
            'email_verified'    => $mail_verification,
            'referral_code'     => $referral_code
        ]);

        if (Setting::get('birthday') == 'on' && $request->birthday != '') {
            $user->settings()->update([ 'birthday' => date('Y-m-d', strtotime($request->birthday)) ]);
        }

        if ($request->gender != '') {
            $user->settings()->update([ 'gender' => $request->gender ]);
        }

        if (Setting::get('city') == 'on' && $request->city != '') {
            $user->settings()->update([ 'city' => $request->city ]);
        }

        if ($user) {

            $this->betaCheck($request, $user);

            // Process invite token if present
            if ( $request->has('invite_token') ) {
                $now = \Carbon\Carbon::now();
                $invite = Invite::where('token', $request->invite_token)->first();

                // Process any shares associated with the invite
                if ($invite && array_key_exists('shareables', $invite->customAttributes??[]) ) {
                    $attrs = [];
                    foreach ( $invite->customAttributes['shareables'] as $s ) {
                        $attrs[] = [
                            'sharee_id' => $user->id,
                            'shareable_type' => $s['shareable_type'],
                            'shareable_id' => $s['shareable_id'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                    DB::table('shareables')->insert($attrs);
                }
                // -> email
                // -> itype
                // -> custom_attributes
                // [ ] how to tie [invites].updated_at to jobs (?)
            }

            if ($request->has('ref')) {
                $referral_user = User::where('referral_code', '=', $request->ref)->first();
                Referral::create([
                    'user_id' => $referral_user->id,
                    'referral_id' => $user->timeline->id,
                    'referral_type' => 'timelines',
                ]);
            }

            $user->notify(new VerifyEmail(
                $user, url(route('verification.verify', ['id' => $user->id, 'hash' => $user->verification_code]))
            ));
            // event(new Registered($user));

            return response()->json(['user' => $user], 201);
        } else {
            abort(400);
        }
    }

    protected function registerUserFromSocialAccount($request)
    {

        //Create user record
        $user = User::create([
            'email'             => $request['email'],
            'password'          => bcrypt($request['password']),
            'verification_code' => str_random(30),
            'remember_token'    => str_random(10),
            'username'          => $request['username'],
            'email_verified'    => 1,
        ]);

        if (Setting::get('birthday') == 'on' && $request['birthday'] != '') {
            $user->settings()->update([ 'birthday' => date('Y-m-d', strtotime($request['birthday'])) ]);
        }

        if ($request['gender'] != '') {
            $user->settings()->update([ 'gender' => $request['gender'] ]);
        }

        if (Setting::get('city') == 'on' && $request['city'] != '') {
            $user->settings()->update([ 'city' => $request['city'] ]);
        }

        // Create timeline record for the user
        $user->timeline()->create([
            'name' => $request['name'],
            'about' => '',
        ]);

        return $user;
    }

    /**
     * Resend the verification notification
     * @param Request $request
     */
    public function resendVerifyEmail(Request $request)
    {
        $user = User::where('email', '=', $request->email)->first();
        if ($user) {
            if ($user->email_verified) {
                return [ 'already_verified' => true ];
            }
            $user->notify(new VerifyEmail(
                $user,
                url(route('verification.verify', ['id' => $user->id, 'hash' => $user->verification_code]))
            ));
        }
        return;
    }

    /**
     * Verify Email Endpoint
     * @param Request $request
     */
    public function verifyEmail(Request $request)
    {
        $user = User::find($request->id);

        if ($user->email_verified) {
            return redirect('/email/verified');
        } else if ($user->verification_code === $request->hash) {
            $user->email_verified = 1;
            $user->email_verified_at = Carbon::now();
            $user->update();
            return redirect('/email/verified');
        } else {
            return redirect('/login');
        }
    }

    public function facebookRedirect(Request $request)
    {
        return Socialite::driver('facebook')->redirect();
    }

    // to get authenticate user data
    public function facebook(Request $request)
    {
        if (Config::get('auth.beta.active')) {
            $request->validate([
                'token' => [ 'required', new \App\Rules\ValidBetaToken ],
            ]);
        }

        // $accessToken = $request->get('code');
        $facebook_user = Socialite::driver('facebook')->user();
        // $token = $driver->getAccessTokenResponse($accessToken);
        //
        // $facebook_user = $driver->getUserByToken($token['access_token']);
        $email = $facebook_user->email;

        if ($email == null) {
            $email = $facebook_user->id.'@facebook.com';
        }

        $user = User::firstOrNew(['email' => $email]);

        if ($facebook_user->name != null) {
            $name = $facebook_user->name;
        } else {
            $name = $email;
        }

        if (!$user->id) {
            $request = [
                'username' => $facebook_user->id,
                'name'     => $name,
                'email'    => $email,
                'password' => bcrypt(str_random(8)),
                'gender'   => 'other',
            ];

            $user = $this->registerUserFromSocialAccount($request);
            //  Prepare the image for user avatar
            if ($facebook_user->getAvatar() != null) {

                $photoName = date('Y-m-d-H-i-s').str_random(8).'.png';
                $mimetype = 'image/png';

                $subFolder = $user->id;
                $s3Path = "$subFolder/$photoName";

                $contents = file_get_contents($facebook_user->avatar_original . "&access_token=".$facebook_user->token);
                Storage::disk('s3')->put($s3Path, $contents);

                $media = Diskmediafile::doCreate([
                    'owner_id'      => $user->id,
                    'filepath'      => $s3Path,
                    'mimetype'      => $mimetype,
                    'orig_filename' => $photoName,
                    'orig_ext'      => 'png',
                    'mfname'        => $photoName,
                    'mftype'        => MediafileTypeEnum::AVATAR,
                    'resource_id'   => $user->id,
                    'resource_type' => 'users',
                ]);
                // $user->timeline->avatar_id = $media->id;
                $user->timeline()->update([ 'avatar_id' => $media->id ]);
            }
        }

        $this->betaCheck($request, $user);

        if (Auth::loginUsingId($user->id)) {
            return redirect('/')->with(['message' => trans('messages.change_username_facebook'), 'status' => 'warning']);
        } else {
            return redirect('/')->with(['message' => trans('messages.user_login_failed'), 'status' => 'success']);
        }
    }

    public function googleRedirect(Request $request)
    {
        return Socialite::driver('google')->redirect();
    }

    // to get authenticate user data
    public function google(Request $request)
    {

        if (Config::get('auth.beta.active')) {
            $request->validate([
                'token' => ['required', new \App\Rules\ValidBetaToken],
            ]);
        }

        $google_user = Socialite::driver('google')->user();
        if (isset($google_user->user['gender'])) {
            $user_gender = $google_user->user['gender'];
        } else {
            $user_gender = 'other';
        }
        $user = User::firstOrNew(['email' => $google_user->user['email']]);

        if (!$user->id) {
            $request = [
                'username' => $google_user->user['id'],
                'name'      => $google_user->user['name'],
                'email'     => $google_user->user['email'],
                'password'  => bcrypt(str_random(8)),
                'gender'    => $user_gender,
            ];

            $user = $this->registerUserFromSocialAccount($request);

            //  Prepare the image for user avatar
            $photoName = date('Y-m-d-H-i-s').str_random(8).'.png';
            $mimetype = 'image/png';

            $subFolder = $user->id;
            $s3Path = "$subFolder/$photoName";

            $contents = file_get_contents($google_user->avatar_original);
            Storage::disk('s3')->put($s3Path, $contents);

            $media = Diskmediafile::doCreate([
                'owner_id'      => $user->id,
                'filepath'      => $s3Path,
                'mimetype'      => $mimetype,
                'orig_filename' => $photoName,
                'orig_ext'      => 'png',
                'mfname'        => $photoName,
                'mftype'        => MediafileTypeEnum::AVATAR,
                'resource_id'   => $user->id,
                'resource_type' => 'users',
            ]);

            // $user->timeline->avatar_id = $media->id;
            $user->timeline()->update([ 'avatar_id' => $media->id ]);
        }

        $this->betaCheck($request, $user);

        if (Auth::loginUsingId($user->id)) {
            return redirect('/')->with(['message' => trans('messages.change_username_google'), 'status' => 'warning']);
        } else {
            return redirect($user->username)->with(['message' => trans('messages.user_login_failed'), 'status' => 'success']);
        }
    }

    public function twitterRedirect()
    {
        return Socialite::driver('twitter')->redirect();
    }

    // to get authenticate user data
    public function twitter(Request $request)
    {

        if (Config::get('auth.beta.active')) {
            $request->validate([
                'token' => ['required', new \App\Rules\ValidBetaToken],
            ]);
        }

        $twitter_user = Socialite::with('twitter')->user();

        if (isset($twitter_user->email)) {
            $email = $twitter_user->email;
        } else {
            $email = $twitter_user->id.'@twitter.com';
        }
        $user = User::firstOrNew(['email' => $email]);
        if (!$user->id) {
            $request = [
                'username'   => $twitter_user->id,
                'name'       => $twitter_user->name,
                'email'      => $email,
                'password'   => bcrypt(str_random(8)),
                'gender'     => 'other',
            ];
            $user = $this->registerUserFromSocialAccount($request);

            //  Prepare the image for user avatar
            $photoName = date('Y-m-d-H-i-s').str_random(8).'.png';
            $mimetype = 'image/png';

            $subFolder = $user->id;
            $s3Path = "$subFolder/$photoName";

            $contents = file_get_contents(str_replace('http://','https://', $twitter_user->avatar_original));
            Storage::disk('s3')->put($s3Path, $contents);

            $media = Diskmediafile::doCreate([
                'owner_id'      => $user->id,
                'filepath'      => $s3Path,
                'mimetype'      => $mimetype,
                'orig_filename' => $photoName,
                'orig_ext'      => 'png',
                'mfname'        => $photoName,
                'mftype'        => MediafileTypeEnum::AVATAR,
                'resource_id'   => $user->id,
                'resource_type' => 'users',
            ]);

            // $user->timeline->avatar_id = $media->id;
            $user->timeline()->update([ 'avatar_id' => $media->id ]);
        }

        $this->betaCheck($request, $user);

        if (Auth::loginUsingId($user->id)) {
            return redirect('/')->with(['message' => trans('messages.change_username_twitter').' <b>'.$user->email.'</b>', 'status' => 'warning']);
        } else {
            return redirect('login')->withInput()->withErrors(['message' => trans('messages.user_login_failed'), 'status' => 'error']);
        }
    }

    /**
     * Checks and uses token if beta program check is required
     * @param mixed $request
     * @param mixed $user
     * @return void
     */
    private function betaCheck($request, $user) {
        if (Config::get('auth.beta.active')) {
            if (Token::useToken($request->token, $user, Config::get('auth.beta.tokenName'))) {
                $user->timeline->cattrs = ['beta_program' => true];
                $user->timeline->save();
            } else {
                // Failed useToken remove user timeline and record
                $user->settings->delete();
                $user->timeline->forceDelete();
                $user->forceDelete();
                abort(400);
            }
        }
    }


}
