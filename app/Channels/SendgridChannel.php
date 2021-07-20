<?php
namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

use App\Apis\Sendgrid\Api as SendgridApi;

// see: https://medium.com/@sirajul.anik/laravel-notifications-part-2-creating-a-custom-notification-channel-6b0eb0d81294
class SendgridChannel
{
    public function send($notifiable, Notification $notification)
    {
        $isSandbox = env('IS_SENDGRID_SANDBOX_ENABLED', false);

        $mdata = $notification->toSendgrid($notifiable);
        //dd($mdata);
        //Log::info("SendgridChannel::send() ".$mdata);

        // Send notification to the $notifiable instance...
        $response = SendgridApi::send($mdata['template_id'], [
            //'subject' => 'Subject Override Ex',
            'to' => $mdata['to'],
            'dtdata' => $mdata['dtdata'] ?? [],
        ], $isSandbox);
        dd($response);

        return $response;
    }
}
