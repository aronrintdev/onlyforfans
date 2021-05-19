<?php

use Illuminate\Support\Facades\Route;

Route::get('/count/subscriptions', 'SubscriptionsController@count')->name('subscriptions.count');

Route::group(['prefix' => '/subscriptions'], function() {
    Route::post('/{subscription}/cancel', 'SubscriptionsController@cancel')->name('subscriptions.cancel');
    Route::get('/{subscription}/restore', 'SubscriptionsController@restore')->name('subscriptions.restore');
    Route::delete('/{subscription}/force', 'SubscriptionsController@forceDelete')->name('subscriptions.forceDelete');
});

Route::apiResource('subscriptions', 'SubscriptionsController', [ 'except' => [ 'store' ] ]);
