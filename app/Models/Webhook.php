<?php

namespace App\Models;

use Log;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Enums\WebhookStatusEnum as Status;

class Webhook extends Model
{
    /** Table name */
    protected $table = 'webhooks';

    /** Property Casts */
    protected $casts = [
        'headers' => 'encrypted:array', // Encrypting in case of sensitive information
        'body'    => 'encrypted:array', // Encrypting in case of sensitive information
        'notes'   => 'encrypted:array', // Encrypting in case of sensitive information
    ];

    /**
     * Store Webhook of unknown origin
     */
    public static function receiveUnknown(Request $request): Response
    {
        Log::info('Received Unknown Webhook');
        $webhook = Webhook::create([
            'type' => 'unknown',
            'origin' => $request->getClientIp(),
            'headers' => $request->headers,
            'verified' => false,
            'body' => $request->all(),
            'status' => Status::IGNORED,
        ]);
        $webhook->save();
        return response('Not authenticated', 401);
    }

    /**
     * Store webhook from SegPay
     */
    public static function receiveSegPay(Request $request): Response
    {
        Log::info('Received SegPay Webhook');

        $webhook = Webhook::create([
            'type' => 'SegPay',
            'origin' => $request->getClientIp(),
            'headers' => $request->headers,
            'verified' => false,
            'body' => $request->all(),
            'status' => Status::UNHANDLED,
        ]);

        // Verify webhook integrity
        if (!Webhook::verifySegPay($request)) {
            // Verification failed
            $webhook->status = Status::IGNORED;
            $webhook->save();
            return response('Not authenticated', 401);
        }

        $webhook->verified = true;

        /**
         * Must be handled synchronously: Probe, Enable, and Disable
         */
        try {
            if (Str::lower($request->action) === 'probe') {
                // Handle Inquiry
            } else if (Str::lower($request->action) === 'enable') {
                // Handle Access Enable
            } else if (Str::lower($request->action) === 'disable') {
                // Handle Access Disable
            }
        } catch (Exception $e) {
            $webhook->status = Status::ERROR;
            $webhook->notes = 'Error on execution: ' . $e->getMessage();
            $webhook->save();
            return response('', 500);
        }


        $webhook->save();

        // TODO: Create job to handle

        return response(); // 200 response
    }

    /**
     * Verify integrity of SegPay webhook request
     */
    public static function verifySegPay(Request $request): bool
    {
        // TODO: Figure out SegPay's verification process
        return false;
    }


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
            'type' => 'Pusher',
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
