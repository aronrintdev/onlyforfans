<?php
namespace App\Http\Controllers\Auth;

//use DB;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Models\Diskmediafile;
use App\Models\Setting;
use App\Models\Timeline;
use App\Models\User;
use App\Models\Invite;
use App\EnumsInviteTypeEnum;
use App\Enums\MediafileTypeEnum;

use File;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Facades\Image;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Storage;
use Theme;
use Validator;

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
            'no_admin' => trans('validation.no_admin'),
            'tos.required' => trans('messages.tos_required')
        ];
        $rules = [
            'email'     => 'required|email|max:255|unique:users',
            // 'name'      => 'max:255',
            // 'gender'    => 'required',
            // 'name'  => 'required|max:25|min:2|unique:timelines|no_admin',
            'username'  => [ 'max:25', 'min:5', 'unique:users', 'no_admin', new \App\Rules\ValidUsername ],
            'password'  => 'required|min:6',
            // 'affiliate' => 'exists:timelines,username',
        ];
        
        if (!$socialLogin) {
            $rules = array_merge($rules, [
                'tos'       => 'required',
            ]);
        }

        if ($captcha) {
            $messages = ['g-recaptcha-response.required' => trans('messages.captcha_required')];
            $rules['g-recaptcha-response'] = 'required';
        }

        return Validator::make($data, $rules, $messages);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
     * @return User
     */
    protected function create(array $data)
    {
        $timeline = Timeline::create([
            'username' => $data['username'],
            'name'     => $data['name'],
        ]);

        return User::create([
            'email'       => $data['email'],
            'password'    => bcrypt($data['password']),
            'timeline_id' => $timeline->id,
        ]);
    }

    public function mainProjectRegister()
    {

        $timeline = Timeline::create([
            'username' => $_GET['username'],
            'name'     => $_GET['fullName'],
            'type'     => 'user',
            'about'    => '',
        ]);

        $user = User::create([
            'email'             => $_GET['email'],
            'password'          => $_GET['password'],
            'timeline_id'       => $timeline->id,
            'gender'            => 'male',
            'affiliate_id'      => null,
            'verification_code' => str_random(30),
            'remember_token'    => str_random(10),
            'email_verified'    => 1,
            'city'              => $_GET['city'],
            'country'           => $_GET['country']
        ]);

        //saving default settings to user settings
        $user_settings = [
            'user_id'               => $user->id,
            'confirm_follow'        => Setting::get('confirm_follow'),
            'follow_privacy'        => Setting::get('follow_privacy'),
            'comment_privacy'       => Setting::get('comment_privacy'),
            'timeline_post_privacy' => Setting::get('user_timeline_post_privacy'),
            'post_privacy'          => Setting::get('post_privacy'),
            'message_privacy'       => Setting::get('user_message_privacy'), ];

        //Create a record in user settings table.
        DB::table('user_settings')->insert($user_settings);

        echo "ok";


    }

    public function mainUserUpdate()
    {

//        $users = User::all();
//        foreach ($users as $user){
//            User::where('id', $user->id)->update([
//                'language'          => 'gr',
//            ]);
//        }

        try {
            User::where('email', $_GET['email'])->update([
                'password'          => bcrypt($_GET['password']),
                'city'              => $_GET['city'],
                'country'           => $_GET['country']
            ]);
            $user = User::where('email', $_GET['email'])->first();

            $timeline = $user->timeline;
            $timeline->update([
                'username' => $_GET['username'],
                'name'     => $_GET['fullName'],
            ]);

            echo 'ok';
        }
        catch(\Exception $e){
            echo $e;
        }

//        DB::table('timelines')->where('id', $privacy->timeline_id)
//            ->update([
//                'username' => $_GET['username'],
//                'name'     => $_GET['fullName'],
//            ]);



    }

    public function register(Request $request)
    {
        if (Auth::user()) {
            return Redirect::to('/');
        }

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('guest');
        return $theme->scope('register', [
            'invite_token' => $request->token ?? null,
        ])->render();
    }

    protected function registerUser(Request $request)
    {

        // %FIXME %TODO: use transaction

        if (Setting::get('captcha') == 'on') {
            $validator = $this->validator($request->all(), true, false);
        } else {
            $validator = $this->validator($request->all(), null, false);
        }

        if ($validator->fails()) {    
            if ($request->ajax()) {
                return response()->json(['err_result' => $validator->errors()->toArray()]);
            }
            return false;
        }

        if(Setting::get('mail_verification') == 'off') {
            $mail_verification = 1;
        } else {
            $mail_verification = 0;
        }
        //Create user record
        $user = User::create([
            'email'             => $request->email,
            'password'          => bcrypt($request->password),
            'verification_code' => str_random(30),
            'username'          => $request->username,
            'remember_token'    => str_random(10),
            'email_verified'    => $mail_verification
        ]);

        if (Setting::get('birthday') == 'on' && $request->birthday != '') {
            $user->birthday = date('Y-m-d', strtotime($request->birthday));
            $user->save();
        }

        if ($request->gender != '') {
            $user->settings->gender = $request->gender;
            $user->settings->save();
        }

        if (Setting::get('city') == 'on' && $request->city != '') {
            $user->city = $request->city;
            $user->save();
        }

        $user->timeline()->create([
            'name' => $request->username,
            'about' => '',
        ]);

        if ($user) {

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

            if (Setting::get('mail_verification') == 'on') {
                Mail::send('emails.welcome', ['user' => $user], function ($m) use ($user) {
                    $m->from(Setting::get('noreply_email'), Setting::get('site_name'));

                    $m->to($user->email, $user->name)->subject('Welcome to '.Setting::get('site_name'));
                });
            }

            if (Auth::loginUsingId($user->id)) {
                return response()->json(['user' => $user], 201);
            } else {
                abort(500);
            }
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
            $user->settings->birthday = date('Y-m-d', strtotime($request['birthday']));
            $user->settings->save();
        }

        if ($request['gender'] != '') {
            $user->settings->gender = $request['gender'];
            $user->settings->save();
        }

        if (Setting::get('city') == 'on' && $request['city'] != '') {
            $user->settings->city = $request['city'];
            $user->settings->save();
        }

        // Create timeline record for the user
        $user->timeline()->create([
            'name' => $request['name'],
            'about' => '',
        ]);
  
        return $user;
           
    }


    public function verifyEmail(Request $request)
    {
        $user = User::where('email', '=', $request->email)->where('verification_code', '=', $request->code)->first();

        if ($user->email_verified) {
            return Redirect::to('login')
            ->with('login_notice', trans('messages.verified_mail'));
        } elseif ($user) {
            $user->email_verified = 1;
            $user->update();
            return Redirect::to('login')
            ->with('login_notice', trans('messages.verified_mail_success'));
        } else {
            echo trans('messages.invalid_verification');
        }
    }

    public function facebookRedirect(Request $request)
    {
        return Socialite::driver('facebook')->redirect();
    }

    // to get authenticate user data
    public function facebook(Request $request)
    {
//        $accessToken = $request->get('code');
        $facebook_user = Socialite::driver('facebook')->user();
//        $token = $driver->getAccessTokenResponse($accessToken);
//
//        $facebook_user = $driver->getUserByToken($token['access_token']);
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
                $timeline = $user->timeline;
                $timeline->avatar_id = $media->id;
                $timeline->save();
            }
        }

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

            $user->timeline->avatar_id = $media->id;
            $user->timeline->save();
        }

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
    public function twitter()
    {
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

            $user->timeline->avatar_id = $media->id;
            $user->timeline->save();
        }

        if (Auth::loginUsingId($user->id)) {
            return redirect('/')->with(['message' => trans('messages.change_username_twitter').' <b>'.$user->email.'</b>', 'status' => 'warning']);
        } else {
            return redirect('login')->withInput()->withErrors(['message' => trans('messages.user_login_failed'), 'status' => 'error']);
        }
    }

    public function linkedinRedirect()
    {
        // return fans::with('linkedin')->redirect();
    }

  // to get authenticate user data
    public function linkedin()
    {
        // $linkedin_user = fans::with('linkedin')->user();

        // $user = User::firstOrNew(['email' => $linkedin_user->email]);
        // if (!$user->id) {
        //     $request = new Request(['username'   => preg_replace('/[^A-Za-z0-9 ]/', '', $linkedin_user->id),
        //       'name'                           => $linkedin_user->name,
        //       'email'                          => $linkedin_user->email,
        //       'password'                       => bcrypt(str_random(8)),
        //       'gender'                         => 'other',
        //     ]);

        //     $timeline = $this->registerUser($request, true);

        //       //  Prepare the image for user avatar
        //     // $avatar = Image::make($linkedin_user->avatar_original);
        //     $photoName = date('Y-m-d-H-i-s').str_random(8).'.png';
        //     // $avatar->save(storage_path().'/uploads/users/avatars/'.$photoName, 60);

        //     $media = Media::create([
        //               'title'  => $photoName,
        //               'type'   => 'image',
        //               'source' => $photoName,
        //             ]);

        //     $timeline->avatar_id = $media->id;

        //     $timeline->save();
        //     $user = $timeline->user;
        // }

        // if (Auth::loginUsingId($user->id)) {
        //     return redirect('/')->with(['message' => trans('messages.change_username_linkedin'), 'status' => 'warning']);
        // } else {
        //     return redirect('login')->with(['message' => trans('messages.user_login_failed'), 'status' => 'error']);
        // }
    }
}
