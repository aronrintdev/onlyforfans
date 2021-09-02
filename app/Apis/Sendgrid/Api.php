<?php
namespace App\Apis\Sendgrid;

use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

use SendGrid\Mail\Mail;
//use Illuminate\Support\Facades\Http;

use App\Models\User;

// ref
//  ~ https://github.com/sendgrid/sendgrid-php
//  ~ https://github.com/sendgrid/sendgrid-php/blob/main/USE_CASES.md#kitchen-sink
class Api
{
    private static $fromEmail = 'no-reply@allfans.com';
    private static $fromName = 'AllFans';

    public static function send(string $templateID, array $attrs)
    {
        $slugs2IDs = self::getSlugs2IDs();
        if ( array_key_exists($templateID, $slugs2IDs) ) {
            // If it's a key in the template array, it's a slug used to lookup the actual SendGrid ID,
            // otherwise it's the raw ID
            $templateID = $slugs2IDs[$templateID];
        }
        $email = new Mail();
        $email->setTemplateId($templateID);
        $email->setFrom(self::$fromEmail, self::$fromName);

        if ( Config::get('sendgrid.debug.override_to_email', false) ) {
            $attrs['to']['email'] = Config::get('sendgrid.debug.override_to_email');
            $attrs['to']['name'] = 'TEST Recepient';
            //$email->addTo( 'peter+receiver@peltronic.com', 'Example Receiver');
        }
        $email->addTo( $attrs['to']['email'], $attrs['to']['name']??null );

        if ( array_key_exists('subject', $attrs) && !empty($attrs['subject']) ) {
            $email->setSubject($attrs['subject']);
        }

        // 'dynamic template data'
        if ( array_key_exists('dtdata', $attrs) && count($attrs['dtdata']) ) {
            $email->addDynamicTemplateDatas( $attrs['dtdata'] );
        }

        $isSandbox = Config::get('sendgrid.debug.enable_sandbox_mode', false);
        if ($isSandbox) {
            dump('SendGrid - WARNING SANDBOX ENABLED!');
            $email->enableSandBoxMode();
        }
        //$email->disableSandBoxMode();

        $sendgrid = new \SendGrid( Config::get('sendgrid.api_key') );
        try {
            $response = $sendgrid->send($email);
            $rdata = [
                'status' => $response->statusCode(),
                'body' => $response->body(),
            ];
            //print_r($response->headers());
            //Log::info('\App\Apis\Sendgrid\Api::send() '.json_encode($rdata, JSON_PRETTY_PRINT));
            //Log::info('\App\Apis\Sendgrid\Api::send() '.json_encode($rdata));
        } catch (Exception $e) {
            Log::error('\App\Apis\Sendgrid\Api::send() - Exception: '.json_encode([
                'rdata' => $rdata ?? null,
                'e.message' => $e->getMessage(),
            ]));
            dd($e);
            throw $e;
        }
        //dd($response, 'response');
        return $response;
    }

    private static function getSlugs2IDs() {
        return [
            // basic-site-functions
            'password-reset'                                           => 'd-fdb56b7baf2e4425b75a08de3b7d31d0', 
            'password-changed-confirmation'                            => 'd-da5cbea0203f49a9b840ac0fc25d3db7', 
            'id-verification-pending'                                  => 'd-ea0736e507534c8e8986947b1c2ddbb6', 
            'id-verification-approved'                                 => 'd-22e9d23524af4edca5e88050c3c89cce', 
            'id-verification-rejected'                                 => 'd-3e62c94809be4b299065b7936c903826', 
            'verify-email'                                             => 'd-4081805e42434ccc80a659e85f5f17d6',  // sent to user immediatley after they register, with link to verify email
            'email-verified'                                           => 'd-02356e4d3fd64beaa85c859368736a98',  // sent after they confirm/verify their email

            'invite-staff-member'                                      => 'd-d8b8bf0a5b174cf5b1190514b381a15b', // for staff management, invite 'member'
            'invite-staff-manager'                                     => 'd-5c3d0fa1296c47e5aa812b8edf065209', // for staff management, invite 'manager'

            'invite-beta-tester'                                       => 'd-f70c0e1dfa9d48b5b6c1841f4dfe9514', // invite user for beta testing purposes

            'new-subscription-payment-received'                        => 'd-57056459a9644d579284f149bc1862b5', 
            'subscription-renewal-payment-received'                    => 'd-bf035ec8004c404ba4c8234eef7604a1', 
            'subscription-payment-received-from-returning-subscriber'  => 'd-016e550f714d418ea3f75df590734664', 
            'new-campaign-contribution-received'                       => 'd-d1a9c281d0fa47479d55cabe10f1d02d', 
            'new-message-received'                                     => 'd-98578442ff154fda906f51e6db3f5286', 
            'new-referral-received'                                    => 'd-0fc75cd7a59148a0a84a82611d3a5973', 
            'campaign-goal-reached'                                    => 'd-a24f3ec1d11f4bd2b71ba2b9faf30609', 
            'new-tip-received'                                         => 'd-d2220b0b64a442958aad5bdf99dd58e3', 
            'new-comment-received'                                     => 'd-8bc8911ea1424d8591e0ba05f92476f1', 
            'change-percentage-of-gross-earnings'                      => 'd-455641d888d94f228c6d95a9f36f3e8b',
        ];
    }

}
