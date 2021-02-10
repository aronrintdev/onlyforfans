<?php

namespace App\Models;

use Log;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Enums\WebhookStatusEnum as Status;

class Webhook extends Model
{
    /** Table name */
    protected $table = 'webhooks';

    /** Property Casts */
    protected $casts = [
        'headers' => 'array',
        'body' => 'array',
        'notes' => 'array',
    ];

    /**
     * Handles storing of pusher webhook requests
     *
     * @param  Illuminate\Http\Request  $request
     * @return  Illuminate\Http\Response
     */
    public static function receivePusher(Request $request): Response
    {
        Log::info('Received Pusher Webhook');

        $webhook = Webhook::create([
            'origin' => $request->getClientIp(),
            'headers' => $request->headers,
            'verified' => false,
            'body' => $request->all(),
            'status' => Status::UNHANDLED,
        ]);

        // Verify webhook integrity
        if (!Webhook::pusherVerify($request)) {
            // Verification failed
            $webhook->status = Status::IGNORED;
            $webhook->save();
            return response('Not authenticated', 401);
        }

        $webhook->verified = true;
        $webhook->save();

        // Trigger async event to take care of webhook.

        return response(); // 200 response
    }

    /**
     * Verifies pusher webhook request.
     * @param  Illuminate\Http\Request  $request
     * @return  bool
     */
    public static function pusherVerify(Request $request): bool
    {
        $secret = config('broadcasting.connections.pusher.secret');
        if (!$secret) {
            throw new Exception('Pusher secret is not defined');
        }
        $app_key = $request->header('HTTP_X_PUSHER_KEY');
        $signature = $request->header('HTTP_X_PUSHER_SIGNATURE');
        $body = $request->getContent();

        $expected_signature = hash_hmac('sha256', $body, $secret, false);

        return $signature == $expected_signature;
    }
}
