<?php

/**
 * Route definitions related to payment processing and reporting
 */

Route::group(['prefix' => '/payment'], function () {

    // /payment/segpay
    Route::group(['prefix' => '/segpay'], function () {
        /** For Iframe url for item */
        Route::post('/generate-url', 'SegPayController@generatePayPageUrl');
        /** Payment session for segpay segments */
        Route::post('/payment-session', 'SegPayController@getPaymentSession')->name('payments.segpay.getPaymentSession');
        /** Iframe url for subscription with existing card */
        Route::post('/subscription-url', 'SegPayController@generateOneClickSubscriptionPageUrl')->name('payments.segpay.getSubscriptionUrl');
    });

    // /payment/methods
    Route::group(['prefix' => '/methods'], function () {
        Route::get('/', 'PaymentMethodsController@index')->name('payment.methods.index');
        Route::delete('/', 'PaymentMethodsController@remove')->name('payment.methods.remove');

        // /payment/methods/default
        Route::group(['prefix' => '/default'], function () {
            Route::get('/', 'PaymentMethodsController@getDefault')->name('payment.methods.default');
            Route::put('/', 'PaymentMethodsController@setDefault')->name('payment.methods.setDefault');
            Route::delete('/', 'PaymentMethodsController@removeDefault')->name('payment.methods.removeDefault');
        });
    });
});