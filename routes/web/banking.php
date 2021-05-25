<?php
/**
 * Banking routes
 */
use Illuminate\Support\Facades\Route;

Route::match(['put', 'post'], 'bank-accounts/set-default', 'AchAccountController@setDefault')
    ->name('bank-accounts.set-default');
Route::apiResource('bank-accounts', 'AchAccountController');

