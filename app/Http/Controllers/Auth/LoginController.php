<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Session as LoginSession;
use App\Models\Setting;
use App\Models\Timeline;
use App\Models\User;
use DB;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Theme;
use Validator;
use Exception;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    //* Where to redirect users after login.
    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function login(Request $request)
    {
        $request->validate([
            //$this->username() => 'required|string',
            'email' => 'required',
            'password' => 'required',
            'g-recaptcha-response' => 'required|recaptchav3:login,0.5'
        ]);

        $user = User::where('email', $request->email)->first();
        if (empty($user)) {
            $user = User::where('username', $request->email)->first();
        }

        try {
            // User exists check
            if (empty($user)) {
                throw new Exception( config('app.debug', false) ? '(No User)' : 'These credentials do not match our records' );
            }
    
            // Password check
            if (!Hash::check($request->password, $user->password)) {
                throw new Exception( config('app.debug', false) ? '(Bad Password)' : 'These credentials do not match our records' );
            }
    
            // Email verified Check
            if ($user->email_verified === false) {
                throw new Exception( 'Your account email has not verified yet. Please verify your email account before continuing' );
            }

            Auth::login($user, $request->remember ? true : false);
            if ($request->expectsJson()) {
                return response()->json([ 'redirect' => '/' ]);
            } else {
                return redirect('/');
            }
        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['message'=>$e->getMessage()]], 401);
            } else {
                return redirect()->guest('login');
            }
        }

    }

    //
    public function mainProjectLogin(Request $request)
    {
        $session = Session::get('users.profile');
        
        $data = $request->all();
        $validate = Validator::make($data, [
            'email'    => 'required',
            'password' => 'required',
        ]);

        if ($validate->fails()) {
            if ($request->ajax()) {
                return response()->json(['status' => '201', 'err_result' => $validate->errors()->toArray()]);
            }
            $messages = $validate->messages()->all();
            return \redirect()->back()->withInput()->withErrors($validate);
        }

        if (!$validate->passes()) {
            return redirect()->back();
        } else {
            $user = '';
            $nameOrEmail = '';
            $canLogin = false;
            $remember = ($request->remember ? true : false);

            if (filter_var(($request->email), FILTER_VALIDATE_EMAIL)  == true) {
                $nameOrEmail = $request->email;
                $user = DB::table('users')->where('email', $request->email)->first();
            } else {
                $user = DB::table('users')->where('username', $request->email)->first();
                if ($user != null) {
                    $user = DB::table('users')->where('email', $request->email)->first();
                    if ($user) {
                        $nameOrEmail = $user->email;
                    }
                }
            }

            if (Setting::get('mail_verification') == 'off') {
                $canLogin = true;
            } else {
                if ($user != null) {
                    if ($user->email_verified == 1) {
                        $canLogin = true;
                    } else {
//                        return response()->json(['status' => '201', 'message' => trans('messages.verify_mail')]);
                        return redirect()->back();
                    }

                }
            }
        }

        if ($canLogin && Auth::attempt(['email' => $nameOrEmail, 'password' => $request->password], $remember)) {
            // return response()->json(['status' => '200', 'message' => trans('auth.login_success')]);
            //save to loginSessions
            $login_session = new LoginSession();
            $login_session->user_id = Auth::user()->id;
            $login_session->user_name = Auth::user()->username;
            $login_session->ip_address = $_SERVER['REMOTE_ADDR'];
            $login_session->machine_name = gethostname();
            $login_session->os = getOS();
            $login_session->browser = getBrowser();

            // get location
            $PublicIP = get_client_ip();
//            $json     = file_get_contents("http://ipinfo.io/$PublicIP/geo");
//            $json     = json_decode($json, true);
//            $country  = $json['country'];
//            $region   = $json['region'];
//            $city     = $json['city'];
//            $login_session->location = $region." ".$city;
            $login_session->date = date("Y-m-d");
            $login_session->save();
            $session = $request->session()->get('profileUrl');

            if (!empty($session)) {
                return redirect($session);
            } else {
                return redirect('/');
            }
        } else {
            // return response()->json(['status' => '201', 'message' => trans('auth.login_failed')]);
            return redirect()->back()->withInput()->withErrors([
                'invalid' => 'These credentials do not match our records.',
            ]);
        }
    }
}
