<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Webhook;

/**
 * Responsible for handling incoming webhooks from clients.
 */
class WebhooksController extends Controller
{
    /**
     * In point for most webhooks
     *
     * @param  Illuminate\Http\Request  $request
     */
    public function receive(Request $request) {
        \Log::debug('Webhook received', [ '$request' => $request ]);

        // HTTP_X_PUSHER_KEY indicates pusher webhook
        if ($request->header('HTTP_X_PUSHER_KEY')) {
            return Webhook::receivePusher($request);
        }

    }
}
