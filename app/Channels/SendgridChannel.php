<?php
namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

// see: https://medium.com/@sirajul.anik/laravel-notifications-part-2-creating-a-custom-notification-channel-6b0eb0d81294
class SendgridChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toSendgrid($notifiable);

        Log::info("SendgridChannel::send()");

        // Send notification to the $notifiable instance...
    }
}
