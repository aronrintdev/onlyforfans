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
    private static $fromEmail = 'info@allfans.com';
    private static $fromName = 'AllFans NoReply';


    public static function send(string $templateID, array $attrs, $isSandbox=false) 
    {
        $email = new Mail();
        $email->setTemplateId($templateID);
        $email->setFrom(self::$fromEmail, self::$fromName);
        $email->addTo( $attrs['to']['email'], $attrs['to']['name']??null );
        //$email->addTo( 'peter+receiver@peltronic.com', 'Example Receiver');

        if ( array_key_exists('subject', $attrs) && !empty($attrs['subject']) ) {
            $email->setSubject($attrs['subject']);
        }

        // 'dynamic template data'
        if ( array_key_exists('dtdata', $attrs) && count($attrs['dtdata']) ) {
            $email->addDynamicTemplateDatas( $attrs['dtdata'] );
        }

        $FORCE_SANDBOX = env('DEBUG_ENABLE_SENDGRID_SANDBOX_MODE', false);
        if ($FORCE_SANDBOX || $isSandbox) {
            if ($FORCE_SANDBOX) {
                dump('WARNING SANDBOX FORCE-ENABLED!');
            }
            $email->enableSandBoxMode();
        }
        //$email->disableSandBoxMode();

        $sendgrid = new \SendGrid( env('SENDGRID_API_KEY') );
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

    private static function setTemplates() {
        return [
        ];
    }

}
