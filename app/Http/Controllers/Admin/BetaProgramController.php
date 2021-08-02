<?php

namespace App\Http\Controllers\Admin;

use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use App\Apis\Sendgrid\Api as SendgridApi;

class BetaProgramController extends Controller
{

    public function index(Request $request)
    {
        // TODO: Summary of beta program tokens and used tokens
    }

    public function tokens(Request $request)
    {
        return Token::where('for', Config::get('auth.beta.tokenName'))
            ->paginate($request->input('take', Config::get('collections.size.large')));
    }

    public function addTokens(Request $request)
    {
        $request->validate([
            'testers' => 'required|json',
        ]);

        $testers = json_decode($request->testers);

        $tokens = Token::add(
            Config::get('auth.beta.tokenName'),
            count($testers),
            [ 'length' => Config::get('auth.beta.tokenLength') ]
        );

        $usedTokens = new Collection([]);
        foreach ($testers as $tester ) {
            // Send beta program email here
            $token = $tokens->pop();
            $registerUrl = url(route('register', [
                'beta' => 1,
                'token' => $token->token,
                'email' => $tester->email,
            ]));

            $response = SendgridApi::send(Config::get('auth.beta.sendGridTemplateId'), [
                'to' => [
                    'email' => $tester->email,
                    'name' => $tester->name,
                ],
                'dtdata' => [
                    'user_name'              => $tester->name,
                    'login_url'              => $registerUrl,
                    'home_url'               => 'https://www.allfans.com',
                    'privacy_url'            => 'https://www.allfans.com/privacy',
                    'referral_url'           => 'https://www.allfans.com/referrals',
                    'manage_preferences_url' => 'https://www.allfans.com/settings',
                    'unsubscribe_url'        => 'https://www.allfans.com/settings',
                ],
            ]);

            $token->custom_attributes = new Collection([
                'sentTo' => $tester->email,
                'sentToName' => $tester->name,
                'url' => $registerUrl,
            ]);
            $token->save();

            $usedTokens->push([
                'token' => $token,
                'sendGridResponse' => [
                    'code' => $response->statusCode(),
                    'body' => $response->body(),
                ],
            ]);
        }

        return $usedTokens;
    }

}
