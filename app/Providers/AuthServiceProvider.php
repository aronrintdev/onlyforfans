<?php
namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Interfaces\Likeable;
use App\Models\Timeline;
use App\Models\VaultFolder;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Comment::class     => \App\Policies\CommentPolicy::class,
        Likeable::class    => \App\Policies\LikeablePolicy::class,
        Post::class        => \App\Policies\PostPolicy::class,
        Timeline::class    => \App\Policies\TimelinePolicy::class,
        Vault::class       => \App\Policies\VaultPolicy::class,
        VaultFolder::class => \App\Policies\VaultFolderPolicy::class,
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
