<?php

namespace App\Http\Controllers\Admin;

use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

class BetaProgramController extends Controller
{

    public function index(Request $request)
    {
        // TODO: Summary of beta program tokens and used tokens
    }

    public function addTokens(Request $request)
    {
        $request->validate([
            'emails.*' => 'required|email',
        ]);

        $tokens = Token::add(
            Config::get('auth.beta.tokenName'),
            count($request->emails),
            [ 'length' => Config::get('auth.beta.tokenLength') ]
        );

        $usedTokens = new Collection([]);
        foreach ($request->emails as $email ) {
            // Send beta program email here
            $token = $tokens->pop();
            $registerUrl = url(route('register', [
                'beta' => 1,
                'token' => $token->token,
                'email' => $email,
            ]));

            // TODO: Send beta invitation email here

            $token->custom_attributes = new Collection([ 'sentTo' => $email ]);
            $token->save();

            $usedTokens->push($token);
        }

        return $usedTokens;
    }

}
