<?php

use Illuminate\Support\Facades\Route;

Route::put('/{tip}/process', 'TipsController@process')
    ->name('tips.process');

Route::apiResource('tips', 'TipsController');
