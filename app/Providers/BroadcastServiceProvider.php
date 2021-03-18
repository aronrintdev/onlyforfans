<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Contracts\Foundation\Application;


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
            return new \App\Broadcasting\AppBroadcaster($pusher);
        });


        Broadcast::routes();

        /*
         * Authenticate the user's personal channel...
         */
        Broadcast::channel('App.User.*', function ($user, $userId) {
            return (int) $user->id === (int) $userId;
        });

        Broadcast::channel('user-status', function ($user) {
            return (Auth::check()) ? $user : false;
        });

        Broadcast::channel('user.status.{userId}', \App\Broadcasting\UserStatusChannel::class);

        Broadcast::channel('{userId}-message',  function ($user) {
            return (Auth::check()) ? $user : false;
        });
    }
}
