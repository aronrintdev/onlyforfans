<?php
/*
|--------------------------------------------------------------------------
| UsernameRules routes
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => '/username'], function() {
    Route::match(['get', 'post'], '/check/{username?}', 'UsernameRulesController@checkUsername')->name('usernameRules.index');

    // Admin Crud API //
    Route::group(['middleware' => ['auth', 'role:admin']], function() {
        Route::get('/rules', 'UsernameRulesController@index')->name('usernameRules.index');
        Route::get('/rules/{page}', 'UsernameRulesController@list')->name('usernameRules.list');
        Route::get('/rule/new', 'UsernameRulesController@create')->name('usernameRules.create');
        Route::post('/rule', 'UsernameRulesController@store')->name('usernameRules.store');
        Route::get('/rule/{id}', 'UsernameRulesController@show')->name('usernameRules.show');
        Route::match(['put', 'patch'], '/rule', 'UsernameRulesController@update')->name('usernameRules.update');
        Route::delete('/rule', 'UsernameRulesController@destroy')->name('usernameRules.destroy');
    });
});
