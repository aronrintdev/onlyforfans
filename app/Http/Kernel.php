<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\Language::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Spatie\Referer\CaptureReferer::class,
            \App\Http\Middleware\TrustedProxies::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
            \App\Http\Middleware\TrustedProxies::class,
        ],

        'webhook' => [
            // Verify source middleware?
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'               => \Illuminate\Auth\Middleware\Authenticate::class,
        'bindings'           => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can'                => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'              => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle'           => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'editown'            => \App\Http\Middleware\EditOwn::class,
        'publicprofile'      => \App\Http\Middleware\PublicProfile::class,
        'disabledemo'        => \App\Http\Middleware\DisableDemo::class,
        'editgroup'          => \App\Http\Middleware\EditGroup::class,
        'editpage'           => \App\Http\Middleware\EditPage::class,
        'role'               => \Spatie\Permission\Middlewares\RoleMiddleware::class,
        'permission'         => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
        'cors'               => \App\Http\Middleware\Cors::class,
        'editevent'          => \App\Http\Middleware\EditEvent::class,
        'spaMixedRoute'      => \App\Http\Middleware\SpaMixed::class,
    ];
}
