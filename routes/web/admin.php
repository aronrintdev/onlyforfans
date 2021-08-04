<?php

use Illuminate\Support\Facades\Route;

/* -------------------------------------------------------------------------- */
/*                            Administration routes                           */
/* -------------------------------------------------------------------------- */

Route::group(['prefix' => '/n0g1cg9sbx', 'middleware' => ['auth', 'role:admin|super-admin']], function () {

    /* -------------------------------- Role -------------------------------- */
    Route::group(['prefix' => '/role'], function () {
        $controller = 'Admin\RolesController';
        $baseName = 'admin.role.';

        Route::resource('', Admin\RolesController::class)
            ->parameters(['' => 'role'])
            ->except([ 'create', 'edit' ])
            ->names([
                'index'   => $baseName . 'index',
                'store'   => $baseName . 'store',
                'show'    => $baseName . 'show',
                'update'  => $baseName . 'update',
                'destroy' => $baseName . 'destroy',
        ]);

        /* --------------------------- Permissions -------------------------- */
        Route::group(['prefix' => '/{role}/permissions'], function() use($controller, $baseName) {
            Route::get('/', $controller . '@getPermissions')
            ->name($baseName . 'permissions.get');
            Route::post('/assign', $controller . '@assignPermissions')
            ->name($baseName . 'permissions.assign');
            Route::post('/remove', $controller . '@removePermissions')
            ->name($baseName . 'permissions.remove');
        });

        /* ------------------------------ User ------------------------------ */
        Route::get('/{role}/users', $controller . '@getUsers')
            ->name($baseName . 'users');
        Route::get('/user/{user}/roles', $controller . '@getUserRoles')
            ->name($baseName . 'user.get');
        Route::post('/user/assign', $controller . '@assignUserRole')
            ->name($baseName . 'user.assign');
        Route::post('/user/remove', $controller . '@removeUserRole')
            ->name($baseName . 'user.remove');
    });

    /* ----------------------------- Permission ----------------------------- */
    Route::group(['prefix' => '/permission'], function() {
        $controller = 'Admin\PermissionsController';
        $baseName = 'admin.permission.';

        Route::resource('', Admin\PermissionsController::class)
            ->parameters(['' => 'permission'])
            ->except(['create', 'edit'])
            ->names([
                'index'   => $baseName . 'index',
                'store'   => $baseName . 'store',
                'show'    => $baseName . 'show',
                'update'  => $baseName . 'update',
                'destroy' => $baseName . 'destroy',
        ]);
    });


    /* ---------------------------- Beta Program ---------------------------- */

    Route::group(['prefix' => 'beta-program'], function() {
        Route::get('/', 'SpaController@admin')->name('admin.beta-program.index');
        Route::get('/tokens', 'Admin\BetaProgramController@tokens')->name('admin.beta-program.tokens');
        Route::post('/add-tokens', 'Admin\BetaProgramController@addTokens')->name('admin.beta-program.add-tokens');
    });

    Route::get('/analyzer-report', 'Admin\DashboardController@analyzerReport')
        ->name('admin.analyzer-report');
    Route::get('/', 'Admin\DashboardController@index')
        ->name('admin.dashboard');

    /* ------------------------------ Webhooks ------------------------------ */
    Route::apiResource('webhooks', 'Admin\WebhooksController', ['only' => ['index']]);

    /* ----------------------------- SegpayCalls ---------------------------- */
    Route::apiResource('segpay-calls', 'Admin\SegpayCallsController', ['only' => ['index']]);

});
