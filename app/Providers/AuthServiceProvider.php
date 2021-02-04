<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Interfaces\Likeable;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        //'App\Model' => 'App\Policies\ModelPolicy',
        Likeable::class => \App\Policies\LikeablePolicy::class,
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
        //  e.i. all gate-related function return true for user->can() / user->hasPermission()
        Gate::before(function($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // Access to `/laravel-websockets`
        Gate::define('viewWebSocketsDashboard', function ($user = null) {
            return $user->hasPermissionTo('admin.websockets.dashboard.view');
        });

        //
    }
}
