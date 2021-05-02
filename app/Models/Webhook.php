<?php

namespace App\Models;

use Log;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Traits\UsesUuid;
use App\Jobs\ProcessSegPayWebhook;
use App\Enums\WebhookTypeEnum as Type;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use App\Enums\WebhookStatusEnum as Status;

/**
 * @property string $type      The type of webhook this is
 * @property string $origin    IP Address origin of webhook
 * @property bool   $verified  If the webhook has been verified to be authentic
 * @property array  $headers   Headers from the webhook request
 * @property array  $body      The body params from the webhook request
 * @property string $status    The processing status of this webhook
 * @property Array  $notes     Any additional notes for this webhook
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class Webhook extends Model
{
    use UsesUuid;

    /** Table name */
    protected $table = 'webhooks';

    protected $forceCombV4Uuid = true;

    /** Property Casts */
    protected $casts = [
        'headers' => 'encrypted:collection', // Encrypting in case of sensitive information
        'body'    => 'encrypted:collection', // Encrypting in case of sensitive information
        'notes'   => 'encrypted:collection', // Encrypting in case of sensitive information
    ];

    /**
     * Store Webhook of unknown origin
     *
     * @param Request $request
     * @return Response
     */
    public static function receiveUnknown(Request $request): Response
    {
        Log::info('Received Unknown Webhook');
        $webhook = Webhook::create([
            'type' => Type::UNKNOWN,
            'origin' => $request->getClientIp(),
            'headers' => $request->headers,
            'verified' => false,
            'body' => $request->all(),
            'status' => Status::IGNORED,
        ]);
        $webhook->save();
        return response('Not authenticated', 401);
    }

    /* ------------------------------- SegPay ------------------------------- */
    #region SegPay
    /**
     * Store webhook from SegPay
     */
    public static function receiveSegPay(Request $request): Response
    {
        Log::info('Received SegPay Webhook');

        $webhook = Webhook::create([
            'type' => 'SegPay',
            'origin' => $request->getClientIp(),
            'headers' => $request->headers->all(),
            'verified' => false,
            'body' => $request->all(),
            'status' => Status::UNHANDLED,
        ]);

        // Verify webhook integrity
        if (!$webhook->verifySegPay($request)) {
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
            $webhook->notes['error'] = $e->getMessage();
            $webhook->save();
            return response('', 500);
        }

        $webhook->save();

        ProcessSegPayWebhook::dispatch($webhook);

        return response(); // 200 response
    }

    /**
     * Verify integrity of SegPay webhook request
     *
     * @param Request $request
     * @return bool
     */
    public function verifySegPay(Request $request): bool
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            Log::debug('Segpay Webhook Rejected, no username or password provided');
            $this->notes['rejected'] = 'No username or password provided';
            return false;
        }

        if (
            $request->username !== Config::get('segpay.webhook.username')
            || $request->password !== Config::get('segpay.webhook.password')
        ) {
            Log::debug('Segpay Webhook Rejected, incorrect username and password');
            $this->notes['rejected'] = 'Incorrect username and password';
            return false;
        }
        return true;
    }

    #endregion SegPay

    /* ------------------------------- Pusher ------------------------------- */
    #region Pusher
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
            'type' => Type::PUSHER,
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

    #endregion Pusher
}
