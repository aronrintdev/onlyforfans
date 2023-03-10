<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;


class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function map()
    {
        $this->mapWebRoutes();
        $this->mapApiRoutes();
        $this->mapWebhookRoutes();
    }

   /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
            'middleware' => 'web',
            'namespace'  => $this->namespace,
        ], function ($router) {
            require base_path('routes/web.php');
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace'  => $this->namespace.'\\API',
            'prefix'     => 'api',
            'as'         => 'api.',
        ], function ($router) {
            require base_path('routes/api.php');
        });
    }

    protected function mapWebhookRoutes()
    {
        Route::group([
            'middleware' => ['webhook'],
            'namespace' => $this->namespace,
            'prefix' => '/hook',
        ], function () {
            Route::post('receive', 'WebhooksController@receive')->name('webhook.receive');
            // SegPay //
            Route::match(['GET', 'POST'], 'receive/segpay', 'WebhooksController@receiveSegpay')->name('webhook.receive.segpay');
            Route::match(['GET', 'POST', 'PUT'], 'receive/id-merit', 'WebhooksController@receiveIdMerit')->name('webhook.receive.id-merit');
        });
    }
}
