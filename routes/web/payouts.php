<?php

/**
 * Routes for payouts
 */

use Illuminate\Support\Facades\Route;

Route::post('/payouts/request', 'PayoutController@request')
    ->name('payouts.request');
