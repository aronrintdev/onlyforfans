<?php
namespace App\Channels;

use Exception;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

use App\Apis\Sendgrid\Api as SendgridApi;

// see: https://medium.com/@sirajul.anik/laravel-notifications-part-2-creating-a-custom-notification-channel-6b0eb0d81294
class SendgridChannel
{
    public function send($notifiable, Notification $notification)
    {
        $mdata = $notification->toSendgrid($notifiable);

        if ( !array_key_exists('template_id', $mdata) ) {
            throw new Exception('mdata requires key template_id, mdata: '.json_encode($mdata));
        }
        if ( !array_key_exists('to', $mdata) ) {
            throw new Exception('mdata requires key "to", mdata: '.json_encode($mdata));
        }
        //dd($mdata);
        //Log::info("SendgridChannel::send() ".$mdata);

        // Send notification to the $notifiable instance...
        $response = SendgridApi::send($mdata['template_id'], [
            //'subject' => 'Subject Override Ex',
            'to' => $mdata['to'],
            'dtdata' => $mdata['dtdata'] ?? [],
        ]);

        $isSandbox = env('DEBUG_ENABLE_SENDGRID_SANDBOX_MODE', false);
        if ( $response->statusCode() != ($isSandbox?200:202) ) {
            throw new Exception( 'SendgridChannel returned status code '.$response->statusCode().', ('.serialize($response).')' );
        }

        return $response;
    }
}
