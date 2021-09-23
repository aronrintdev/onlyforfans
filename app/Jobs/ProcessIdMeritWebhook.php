<?php

namespace App\Jobs;

use App\Enums\VerifyStatusTypeEnum;
use Exception;
use App\Models\Webhook;
use App\Models\Verifyrequest;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Enums\WebhookStatusEnum as Status;
use App\Notifications\IdentityVerificationRejected;
use App\Notifications\IdentityVerificationVerified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class ProcessIdMeritWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The webhook being processed.
     * @var Webhook
     */
    public $webhook;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Webhook $webhook)
    {
        $this->webhook = $webhook;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug("Processing IdMerit webhook: {$this->webhook->id}");

        // Lock webhook so other jobs don't interfere
        DB::transaction(function () {
            $webhook = Webhook::lockForUpdate()->find($this->webhook->id);
            if ($webhook->status != Status::UNHANDLED) {
                return;
            }
            try {
                $verifyrequest = Verifyrequest::checkStatusByGUID($webhook->body->uniqueId);

                $webhook->status = Status::HANDLED;
                $webhook->handled_at = Carbon::now();
            } catch (Exception $e) {
                $webhook->status = Status::ERROR;
                $webhook->handled_at = Carbon::now();
                $webhook->notes = 'Error on execution: ' . $e->getMessage();
                $webhook->save();
                Log::error('Error on ProcessIdMeritWebhook', ['message' => $e->getMessage(), 'stacktrace' => $e->getTraceAsString()]);
                return;
            }

        });
    }
}
