<?php

/**
 * Route definitions related to payment processing and reporting
 */

Route::group(['prefix' => '/payment'], function () {
    Route::group(['prefix' => '/segpay'], function () {
        Route::post('/generate-url', 'SegPayController@generatePayPageUrl');
    });

    Route::get('/my-payment-methods', 'PaymentsController@myPaymentMethods')->name('payment.paymentMethods.get');
    Route::post('/set-default-method', 'PaymentsController@setDefaultPaymentMethod')->name('payment.paymentMethods.setDefault');

});