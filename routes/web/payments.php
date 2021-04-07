<?php

/**
 * Route definitions related to payment processing and reporting
 */

Route::group(['prefix' => '/payment'], function () {
    Route::group(['prefix' => '/segpay'], function () {
        Route::post('/generate-url', 'SegPayController@generatePayPageUrl');
    });

    Route::get('/methods/index', 'PaymentsController@index')->name('payment.methods.index');
    Route::put('/methods/default', 'PaymentsController@setDefault')->name('payment.methods.setDefault');
    Route::delete('/method', 'PaymentsController@remove')->name('payment.methods.remove');

});