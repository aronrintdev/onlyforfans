<?php
/* -------------------------------------------------------------------------- */
/*                               Webhook Routes                               */
/* -------------------------------------------------------------------------- */

Route::post('hook/receive', 'WebhooksController@receive')->name('webhook.receive');
