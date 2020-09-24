<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where all API routes are defined.
|
*/





Route::resource('timelines', 'TimelineAPIController');
Route::get('search', 'TimelineAPIController@search');

Route::resource('users', 'UserAPIController');

