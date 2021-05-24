<?php
namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Interfaces\Likeable;
use App\Models\Financial\Account;
use App\Models\Mediafile;
use App\Models\Diskmediafile;
use App\Models\Favorite;
use App\Models\Chatthread;
use App\Models\Chatmessage;
use App\Models\Subscription;
use App\Models\Timeline;
use App\Models\Vaultfolder;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Account::class           => \App\Policies\Financial\AccountPolicy::class,
        Chatmessage::class       => \App\Policies\ChatmessagePolicy::class,
        Chatthread::class        => \App\Policies\ChatthreadPolicy::class,
        Comment::class           => \App\Policies\CommentPolicy::class,
        Diskmediafile::class     => \App\Policies\DiskmediafilePolicy::class,
        Favorite::class          => \App\Policies\FavoritePolicy::class,
        Likeable::class          => \App\Policies\LikeablePolicy::class,
        Post::class              => \App\Policies\PostPolicy::class,
        Mediafile::class         => \App\Policies\MediafilePolicy::class,
        Post::class              => \App\Policies\PostPolicy::class,
        Subscription::class      => \App\Policies\SubscriptionPolicy::class,
        Timeline::class          => \App\Policies\TimelinePolicy::class,
        Vault::class             => \App\Policies\VaultPolicy::class,
        Vaultfolder::class       => \App\Policies\VaultfolderPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @param \Illuminate\Contracts\Auth\Access\Gate $gate
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Implicitly grant "Super Admin" role all permissions
        //  e.i. all gate-related function return true for user->can()
        Gate::before(function($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        // Access to `/laravel-websockets`
        Gate::define('viewWebSocketsDashboard', function ($user = null) {
            return $user->can('admin.websockets.dashboard.view');
        });

        //
    }
}
