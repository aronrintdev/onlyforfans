<?php

namespace App\Jobs;

use App\Apis\Segpay\Enums\Action;
use App\Apis\Segpay\Enums\TransactionType;
use App\Apis\Segpay\Transaction;
use Exception;
use App\Models\Webhook;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Enums\WebhookStatusEnum as Status;
use App\Models\Financial\SegpayCard;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;

class ProcessSegPayWebhook  implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Webhook instance
     * @var \App\Models\Webhook
     */
    protected $webhook;

    /**
     * Create a new job instance.
     *
     * @param  App\Models\Webhook  $webhook
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
        // Lock webhook so other jobs don't interfere
        DB::transaction(function () {
            $webhook = Webhook::lockForUpdate()->find($this->webhook->id);
            if ($webhook->status != Status::UNHANDLED) {
                return;
            }
            try {
                $transaction = new Transaction($webhook->body);
                switch ($transaction->action) {
                    /** Inquiry */
                    case Action::PROBE:
                        $this->handleProbe($transaction);
                        break;
                    /** Access Enable */
                    case Action::ENABLE:
                        $this->handleEnable($transaction);
                        break;
                    /** Access Disable */
                    case Action::DISABLE:
                        $this->handleDisable($transaction);
                        break;
                    /** Cancellation */
                    case Action::CANCEL:
                        $this->handleCancel($transaction);
                        break;
                    /** Reactivation */
                    case Action::REACTIVATION:
                        $this->handleReactivation($transaction);
                        break;
                    /** Transaction */
                    case Action::AUTH:
                        $this->handleAuth($transaction);
                        break;
                    case Action::VOID:
                        $this->handleVoid($transaction);
                        break;
                }
            } catch (Exception $e) {
                $webhook->status = Status::ERROR;
                $webhook->notes = 'Error on execution: ' . $e->getMessage();
                $webhook->save();
                return;
            }
            $webhook->status = Status::HANDLED;
            $webhook->save();
        });
    }

    /**
     * Handle action: `probe` hook
     *
     * *From Documentation:*
     * > A username and/or password was collected during payment. Segpay is making sure the username is available in
     * > your system. If it isn’t, we will assign the consumer's email address as the username.
     */
    private function handleProbe($transaction)
    {
        //
    }

    /**
     * Handle action: `enable` hook
     *
     * *From Documentation:*
     * > Access to your system has been granted, following a purchase.
     */
    private function handleEnable($transaction)
    {
        //
    }

    /**
     * Handle action: `disable` hook
     *
     * *From Documentation:*
     * > Access to your system has been removed, following an account cancellation/expiration.
     */
    private function handleDisable($transaction)
    {
        //
    }

    /**
     * Handle action `cancel` hook
     *
     * *From Documentation:*
     * > A member has requested a cancellation or a refund/cancellation.
     */
    private function handleCancel($transaction)
    {
        //
    }

    /**
     * Handle action `reactivation` hook
     *
     * *From Documentation:*
     * > A cancelled or expired subscription has been reactivated.
     */
    private function handleReactivation($transaction)
    {
        //
    }

    /**
     * Handle action: `auth` hook
     *
     * *From Documentation:*
     * > An authorization has occurred.
     */
    private function handleAuth($transaction)
    {
        if ($transaction->transactionType === TransactionType::SALE) {
            // Check if user has CC account already
            $card = SegpayCard::findByToken($transaction->purchaseId);
            if (!isset($card)) {
                // Create new card and account for this.
                $card = SegpayCard::createFromTransaction($transaction);
            }

            // Move to user internal account
            $buyer = $card->getOwner()->first();
            $card->account->moveToInternal($transaction->price);

            // Move to resource internal account

        } else if ($transaction->transactionType === TransactionType::CHARGE) {
            // Chargeback
        } else if ($transaction->transactionType === TransactionType::CREDIT) {
            // Refund
        }
    }

    /**
     * Handle action: `void` hook
     *
     * *From Documentation:*
     * > A void has occurred.
     */
    private function handleVoid($transaction)
    {
        //
    }

}