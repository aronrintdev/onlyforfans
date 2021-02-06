<?php

Route::group(['prefix' => '/{username}/settings', 'middleware' => ['auth', 'editown']], function ($username) {
    Route::get('/general', 'ZDeprecated\UserController@userGeneralSettings');
    Route::post('/general', 'ZDeprecated\UserController@saveUserGeneralSettings');
    
    Route::post('/localization', 'ZDeprecated\UserController@saveUserLocalizationSettings');
    Route::post('/subscription', 'ZDeprecated\UserController@saveUserSubscriptionSettings');
    
    Route::get('/profile', 'ZDeprecated\UserController@userEditProfile');
    Route::post('/profile', 'ZDeprecated\UserController@saveProfile');

    Route::get('/privacy', 'ZDeprecated\UserController@userPrivacySettings');
    Route::post('/privacy', 'ZDeprecated\UserController@SaveUserPrivacySettings');
    
    Route::get('/security', 'ZDeprecated\UserController@userSecuritySettings');
    Route::post('/security', 'ZDeprecated\UserController@SaveUserSecuritySettings');
    
    Route::post('/block-profile', 'ZDeprecated\UserController@blockProfile')->name('block-profile');
    Route::post('/update-block-profile', 'ZDeprecated\UserController@updateBlockProfile')->name('update-block-profile');
    Route::get('edit-block-profile/{id}', 'ZDeprecated\UserController@editBlockProfile')->where('id', '[0-9]+');
    Route::delete('delete-block-profile/{id}', 'ZDeprecated\UserController@deleteBlockProfile')->where('id', '[0-9]+');

    Route::get('/wallpaper', 'ZDeprecated\UserController@wallpaperSettings');
    Route::post('/wallpaper', 'ZDeprecated\TimelineController@saveWallpaperSettings');
    Route::get('/toggle-wallpaper/{action}/{media}', 'ZDeprecated\TimelineController@toggleWallpaper');

    Route::get('/password', 'ZDeprecated\UserController@userPasswordSettings');
    Route::post('/password', 'ZDeprecated\UserController@saveNewPassword');

    Route::get('/affliates', 'ZDeprecated\UserController@affliates');
    Route::get('/login_sessions', 'ZDeprecated\UserController@loginSessions');

    Route::get('/deactivate', 'ZDeprecated\UserController@deactivate');
    Route::get('/deleteme', 'ZDeprecated\UserController@deleteMe');

    Route::get('/notifications', 'ZDeprecated\UserController@emailNotifications');
    Route::post('/notifications', 'ZDeprecated\UserController@updateEmailNotifications');
    
    Route::get('/addbank', 'ZDeprecated\UserController@addBank');
    Route::post('/addbank', 'ZDeprecated\UserController@addBank');
    
    Route::get('/earnings', 'ZDeprecated\UserController@earnings');
    Route::post('/earnings', 'ZDeprecated\UserController@earnings');
    
    Route::post('/bankdetails', 'ZDeprecated\UserController@saveBankAccountDetails');
    
    Route::get('/addpayment', 'ZDeprecated\UserController@addPayment');
    Route::post('/addpayment', 'ZDeprecated\UserController@addPayment');

    Route::get('/save-payment-details', 'ZDeprecated\UserController@saveUserPaymentDetails');
    Route::post('/save-payment-details', 'ZDeprecated\UserController@saveUserPaymentDetails');

    Route::get('/save-bank-details', 'ZDeprecated\UserController@saveUserBankDetails');
    Route::post('/save-bank-details', 'ZDeprecated\UserController@saveUserBankDetails');
    
    Route::post('/save-watermark-settings', 'ZDeprecated\UserController@saveWaterMarkSetting');

});
