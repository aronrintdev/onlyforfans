<?php

namespace App\Providers;

use App\Models\Setting;
use App\Payments\PaymentGateway;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Validator;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Guard $auth)
    {
        Validator::extend('not_contains', function($attribute, $value, $parameters)
        {
            // Banned words
            $words = explode(",",Setting::get('censored_words'));
            foreach ($words as $word)
            {
                if (stripos($value, $word) !== false) return false;
            }
            return true;
        });

        Validator::extend('no_admin', function($attribute, $value, $parameters)
        {
            if (stripos($value, 'admin') !== false) return false;
            return true;
        });


        if (env('APP_ENV', 'local') !== 'local') {
            DB::connection()->disableQueryLog();
        }

        // if(Schema::hasTable('settings')) {
        //     App::setLocale(Setting::get('language', 'en'));
        // }
        // else {
        //     App::setLocale('en');
        // }

        if (Schema::hasTable('settings')) {
            // Config::set('app.timezone', Setting::get('timezone', 'UTC'));
            Config::set('app.timezone', date_default_timezone_get());
        }

        Relation::morphMap([
            'chatmessages'           => 'App\Models\Chatmessage',
            'chatthreads'            => 'App\Models\Chatthread',
            'comments'               => 'App\Models\Comment',
            'conversations'          => 'App\Models\Conversation',
            //'favorites'              => 'App\Models\Favorite',
            'invites'                => 'App\Models\Invite',
            'links'                  => 'App\Models\Link',
            'locations'              => 'App\Models\Location',
            'mediafiles'             => 'App\Models\Mediafile',
            'permissions'            => 'App\Models\Permission',
            'posts'                  => 'App\Models\Post',
            'roles'                  => 'App\Models\Role',
            'segpay_card'            => 'App\Models\Financial\SegpayCard',
            'sessions'               => 'App\Models\Session',
            'settings'               => 'App\Models\Setting',
            'financial_accounts'     => 'App\Models\Financial\Account',
            'financial_ach_account'  => 'App\Models\Financial\AchAccount',
            'financial_flags'        => 'App\Models\Financial\Flag',
            'payout_batches'         => 'App\Models\Financial\PayoutBatch',
            'financial_summaries'    => 'App\Models\Financial\TransactionSummary',
            'financial_system_owner' => 'App\Models\Financial\SystemOwner',
            'financial_transactions' => 'App\Models\Financial\Transaction',
            'stories'                => 'App\Models\Story',
            'subscriptions'          => 'App\Models\Subscription',
            'timelines'              => 'App\Models\Timeline',
            'tips'                   => 'App\Models\Tip',
            'users'                  => 'App\Models\User',
            'usernameRules'          => 'App\Models\UsernameRule',
            'vaults'                 => 'App\Models\Vault',
            'vaultfolders'           => 'App\Models\Vaultfolder',
            'webhooks'               => 'App\Models\Webhook',
            'messages'               => 'App\Models\Message',
        ]);

        App::singleton(PaymentGateway::class, function($app) {
            return new PaymentGateway();
        });

        Carbon::serializeUsing(function ($carbon) {
            return $carbon->toISOString();
        });

        // WebSocket Router
        $this->app->singleton('websockets.router', function () {
            return new \App\WebSockets\Router();
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
