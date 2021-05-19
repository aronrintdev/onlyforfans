<?php

/**
 * Route definitions related to payment processing and reporting
 */

Route::group(['prefix' => '/payment'], function () {
    Route::group(['prefix' => '/segpay'], function () {
        Route::post('/generate-url', 'SegPayController@generatePayPageUrl');
        Route::post('/payment-session', 'SegPayController@getPaymentSession')->name('payments.segpay.getPaymentSession');
        
        Route::post('/subscription-url', 'SegPayController@generateOneClickSubscriptionPageUrl')->name('payments.segpay.getSubscriptionUrl');

        Route::post('/fake', 'SegPayController@fake')->name('payments.segpay.fake');
        Route::post('/fake-confirmation', 'SegPayController@fakeConfirmation')->name('payments.segpay.fake-confirmation');
        Route::post('/confirmation', 'SegPayController@paymentConfirmation')->name('payments.segpay.confirmation');
    });

    Route::post('/purchase', 'PaymentsController@purchase')->name('payments.purchase');

    Route::get('/methods/index', 'PaymentsController@index')->name('payment.methods.index');
    Route::put('/methods/default', 'PaymentsController@setDefault')->name('payment.methods.setDefault');
    Route::delete('/method', 'PaymentsController@remove')->name('payment.methods.remove');

});