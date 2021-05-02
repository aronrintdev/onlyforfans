<?php
/**
 * Routes related to webhooks
 */

/*
|--------------------------------------------------------------------------
| Webhook
|--------------------------------------------------------------------------
*/

Route::post('hook/receive', 'WebhooksController@receive')->name('webhook.receive');

// SegPay //
Route::post('hook/receive/segpay', 'WebhooksController@receiveSegpay')->name('webhook.receive.segpay');
