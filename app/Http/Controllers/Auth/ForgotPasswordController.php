<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Models\User;
use App\Models\Setting;


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

        if (!isset($user)) {
            return response()->json(['message' => trans('User does not exist')]);
        }

        $token = str_random(60);
        $verify_link = config('base_url') . 'password/reset/' . $token . '?email=' . urlencode($user->email);

        Mail::send('emails.reset_password', ['user' => $user, 'link' => $verify_link], function ($m) use ($user) {
            
            $m->to($user->email)->subject('Forgot Password');
        });
        
        return response()->json(['message' => 'A reset link has been sent to your email address.']);
    }
}
