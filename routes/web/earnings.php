<?php

use Illuminate\Support\Facades\Route;

Route::get('/earnings', 'EarningsController@index')->name('earnings.index');
Route::get('/earnings/balance', 'EarningsController@balances')->name('earnings.balances');
Route::get('/earnings/transactions', 'EarningsController@transactions')->name('earnings.transactions');
