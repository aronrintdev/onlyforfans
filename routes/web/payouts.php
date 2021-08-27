<?php

/**
 * Routes for payouts
 */

use Illuminate\Support\Facades\Route;

Route::post('/payouts/request', 'PayoutController@request')
    ->name('payouts.request');

Route::get('/payouts/transactions', 'PayoutController@transactions')
    ->name('payouts.transactions');
