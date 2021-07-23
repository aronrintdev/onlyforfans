<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Models\User;
use App\Models\Setting;
use Validator;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function sendResetEmail ($email, $token) {
        
    }

    public function store (Request $request) {
        $request->validate(['email' => 'required|email']);

        $email = $request->input('email');

        $user = User::where('email', $email)->first();

        if (isset($user)) {
            $token = str_random(30);
            $user->remember_token = $token;
            $user->save();

            $verify_link = config('base_url') . 'reset-password/' . $token . '?email=' . urlencode($user->email);

            Mail::send('emails.reset_password', ['user' => $user, 'link' => $verify_link], function ($m) use ($user) {
                
                $m->to($user->email)->subject('Forgot Password');
            });
        }

        // return response()->json(['message' => 'User does not exist.'], 400);
        return response()->json(['message' => 'If we found a match in our database, an email has been dispatched with instructions to reset your password.']);
    }

    public function checkResetToken (Request $request, $token) {
        $email = $request->query('email');

        $user = User::where('email', $email)->where('remember_token', $token)->first();
        if (isset($user)) {
            return response()->json(['status' => 200, 'message' => 'Token is valid']);
        } else {
            return response()->json(['status' => 400, 'message' => 'Token or Email are invalid.']);
        }
    }
    
    public function resetPass (Request $request) {
        $rules = [
            'email'     => 'required|email|max:255',
            'password'  => 'required|min:6',
        ];
        $password = $request->input('password');
        $email = $request->input('email');

        $validator = Validator::make([
            'password' => $password,
            'email' => $email,
        ], $rules, []);

        if ($validator->fails()) {    
            if ($request->ajax()) {
                return response()->json(['status' => 201, 'err_result' => $validator->errors()->toArray()]);
            }
            return false;
        }

        $user = User::where('email', $email)->first();

        if ($user->password == bcrypt($password)) {
            return response()->json(['status' => 400, 'message' => 'New password should not equal to old one.']);
        }

        if (isset($user)) {
            $user->password = bcrypt($password);
            $user->remember_token = '';
            $user->save();
            return response()->json(['status' => 200, 'message' => 'You have successfully reset your password.']);
        } else {
            return response()->json(['status' => 400, 'message' => 'Reset Password is failed. Please try again.']);
        }
    }
}
