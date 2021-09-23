<?php

namespace App\Http\Controllers;

use Log;
use Illuminate\Http\Request;
use App\Models\Webhook;
use Illuminate\Support\Facades\Config;

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
        Log::debug('Webhook received', [ '$request' => $request ]);

        // HTTP_X_PUSHER_KEY indicates pusher webhook
        if ($request->header('HTTP_X_PUSHER_KEY')) {
            return Webhook::receivePusher($request);
        }
    }

    /**
     * Receive point for a webhook from Segpay
     * @param Request $request
     * @return void
     */
    public function receiveSegpay(Request $request) {
        return Webhook::receiveSegPay($request);
    }

    public function receiveIdMerit(Request $request) {
        return Webhook::receiveIdMerit($request);
    }
}
