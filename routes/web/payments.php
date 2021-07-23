<?php

/**
 * Route definitions related to payment processing and reporting
 */

Route::group(['prefix' => '/payment'], function () {
    Route::group(['prefix' => '/segpay'], function () {
        /** For Iframe url for item */
        Route::post('/generate-url', 'SegPayController@generatePayPageUrl');
        /** Payment session for segpay segments */
        Route::post('/payment-session', 'SegPayController@getPaymentSession')->name('payments.segpay.getPaymentSession');
        /** Iframe url for subscription with existing card */
        Route::post('/subscription-url', 'SegPayController@generateOneClickSubscriptionPageUrl')->name('payments.segpay.getSubscriptionUrl');
    });

    Route::get('/methods', 'PaymentMethodsController@index')->name('payment.methods.index');
    Route::put('/methods/default', 'PaymentMethodsController@setDefault')->name('payment.methods.setDefault');
    Route::delete('/methods', 'PaymentMethodsController@remove')->name('payment.methods.remove');

});