<?php
namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Contracts\Foundation\Application;

use App\Broadcasting\UserPurchasesChannel;
use App\Broadcasting\UserEventsChannel;
use App\Broadcasting\UserStatusChannel;
use App\Models\Chatthread;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(BroadcastManager $broadcastManager)
    {
        // Custom Broadcaster driver with pusher
        $broadcastManager->extend('app', function(Application $app, array $config) {
            $pusher = new \Pusher\Pusher(
                $config['key'], // $auth_key,
                $config['secret'], // $secret,
                $config['app_id'], // $app_id,
                $config['options'], // $options = array(),
            );
            if (Config::get('broadcasting.connections.app.options.debug', false)) {
                $pusher->setLogger(Log::getLogger());
            }
            return new \App\Broadcasting\AppBroadcaster($pusher);
        });

        Broadcast::routes();

        // %NOTE: channel authorization is done here instead of routes/channels.php
        //  ~ https://laravel.com/docs/8.x/broadcasting#defining-authorization-callbacks
        //  ~ https://mattstauffer.com/blog/introducing-laravel-echo/
        Broadcast::channel('user.status.{userId}'   , UserStatusChannel::class);
        Broadcast::channel('user.{userId}.purchases', UserPurchasesChannel::class);
        Broadcast::channel('user.{userId}.events'   , UserEventsChannel::class);

        // private-chatthreads.{chatthreadId} ??
        Broadcast::channel('chatthreads.{chatthread}', function($sessionUser, Chatthread $chatthread) {
            //dd('provider.abc.1');
            return true; // %TODO %FIXME
        });

        Broadcast::channel('chat-typing', function ($sessionUser) {
            return (Auth::check()) ? $sessionUser : false;
        });

        //Broadcast::channel('{userId}-message',  function ($user) {
        //    return (Auth::check()) ? $user : false;
        //});
        //Broadcast::channel('{userId}-message-published',  function ($user) {
        //    return (Auth::check()) ? $user : false;
        //});
    }
}
