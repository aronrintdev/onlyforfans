<?php

Route::group(['prefix' => '/{username}/settings', 'middleware' => ['auth', 'editown']], function ($username) {
    Route::get('/general', 'UserController@userGeneralSettings');
    Route::post('/general', 'UserController@saveUserGeneralSettings');
    
    Route::post('/localization', 'UserController@saveUserLocalizationSettings');
    Route::post('/subscription', 'UserController@saveUserSubscriptionSettings');
    
    Route::get('/profile', 'UserController@userEditProfile');
    Route::post('/profile', 'UserController@saveProfile');

    Route::get('/privacy', 'UserController@userPrivacySettings');
    Route::post('/privacy', 'UserController@SaveUserPrivacySettings');
    
    Route::get('/security', 'UserController@userSecuritySettings');
    Route::post('/security', 'UserController@SaveUserSecuritySettings');
    
    Route::post('/block-profile', 'UserController@blockProfile')->name('block-profile');
    Route::post('/update-block-profile', 'UserController@updateBlockProfile')->name('update-block-profile');
    Route::get('edit-block-profile/{id}', 'UserController@editBlockProfile')->where('id', '[0-9]+');
    Route::delete('delete-block-profile/{id}', 'UserController@deleteBlockProfile')->where('id', '[0-9]+');

    Route::get('/wallpaper', 'UserController@wallpaperSettings');
    Route::post('/wallpaper', 'TimelineController@saveWallpaperSettings');
    Route::get('/toggle-wallpaper/{action}/{media}', 'TimelineController@toggleWallpaper');

    Route::get('/password', 'UserController@userPasswordSettings');
    Route::post('/password', 'UserController@saveNewPassword');

    Route::get('/affliates', 'UserController@affliates');
    Route::get('/login_sessions', 'UserController@loginSessions');

    Route::get('/deactivate', 'UserController@deactivate');
    Route::get('/deleteme', 'UserController@deleteMe');

    Route::get('/notifications', 'UserController@emailNotifications');
    Route::post('/notifications', 'UserController@updateEmailNotifications');
    
    Route::get('/addbank', 'UserController@addBank');
    Route::post('/addbank', 'UserController@addBank');
    
    Route::get('/earnings', 'UserController@earnings');
    Route::post('/earnings', 'UserController@earnings');
    
    Route::post('/bankdetails', 'UserController@saveBankAccountDetails');
    
    Route::get('/addpayment', 'UserController@addPayment');
    Route::post('/addpayment', 'UserController@addPayment');

    Route::get('/save-payment-details', 'UserController@saveUserPaymentDetails');
    Route::post('/save-payment-details', 'UserController@saveUserPaymentDetails');

    Route::get('/save-bank-details', 'UserController@saveUserBankDetails');
    Route::post('/save-bank-details', 'UserController@saveUserBankDetails');
    
    Route::post('/save-watermark-settings', 'UserController@saveWaterMarkSetting');

});
