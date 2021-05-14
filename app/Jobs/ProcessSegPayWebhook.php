<?php

namespace App\Jobs;

use Exception;
use Throwable;
use Money\MoneyParser;
use App\Models\Webhook;
use App\Events\TipFailed;
use App\Helpers\Tippable;
use Illuminate\Support\Str;
use App\Helpers\Purchasable;
use App\Helpers\Subscribable;
use Illuminate\Bus\Queueable;
use App\Enums\PaymentTypeEnum;
use App\Events\ItemSubscribed;
use App\Events\PurchaseFailed;
use Illuminate\Support\Carbon;
use App\Apis\Segpay\Transaction;
use App\Apis\Segpay\Enums\Action;
use App\Events\SubscriptionFailed;
use App\Models\Traits\FormatMoney;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Models\Financial\SegpayCall;
use App\Models\Financial\SegpayCard;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Apis\Segpay\Enums\TransactionType;
use App\Enums\WebhookStatusEnum as Status;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Apis\Segpay\Enums\Stage as StageEnum;
use App\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;

class ProcessSegPayWebhook implements ShouldQueue
{
    use Dispatchable,
    InteractsWithQueue,
    Queueable,
    SerializesModels,
    FormatMoney;

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
        Log::debug("Processing webhook {$this->webhook->id}");

        // Lock webhook so other jobs don't interfere
        DB::transaction(function () {
            $webhook = Webhook::lockForUpdate()->find($this->webhook->id);
            if ($webhook->status != Status::UNHANDLED) {
                return;
            }
            try {
                $transaction = new Transaction($webhook->body);
                switch (Str::lower($transaction->action)) {
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
                $webhook->handled_at = Carbon::now();
                $webhook->notes = 'Error on execution: ' . $e->getMessage();
                $webhook->save();
                Log::error('Error on ProcessSegPayWebhook', [ 'message' => $e->getMessage(), 'stacktrace' => $e->getTraceAsString() ]);
                return;
            }
            $webhook->status = Status::HANDLED;
            $webhook->handled_at = Carbon::now();
            $webhook->save();
        });
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        Log::error('ProcessSegPayWebhook Failed', [
            'message' => $exception->getMessage(),
            'stacktrace' => $exception->getTraceAsString()
        ]);
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
        if (Str::lower($transaction->transactionType) === TransactionType::SALE) {
            // Check for reference_id
            if (isset($transaction->reference_id)) {
                $segpayCall = SegpayCall::find($transaction->reference_id);
                $item = $segpayCall->resource;
            } else {
                if ($transaction->item_type === PaymentTypeEnum::PURCHASE) {
                    $item = Purchasable::getPurchasableItem($transaction->item_id);
                } else if ($transaction->item_type === PaymentTypeEnum::TIP) {
                    $item = Tippable::getTippableItem($transaction->item_id);
                } else if ($transaction->item_type === PaymentTypeEnum::SUBSCRIPTION) {
                    $item = Subscribable::getSubscribableItem($transaction->item_id);
                }
            }


            // Check if user has CC account already
            if (isset($transaction->octoken)) {
                $card = SegpayCard::findByToken($transaction->octoken);
                Log::debug('Processing from known card', [ 'card' => $card->id ]);
            }
            if (!isset($card)) {
                // Create new card and account for this.
                $card = SegpayCard::createFromTransaction($transaction);
            }

            // Translate decimal price from segpay back to money type
            $price = $this->parseMoneyDecimal($transaction->price, $transaction->currencyCode);

            // -- Purchase -- //
            if ($transaction->item_type === PaymentTypeEnum::PURCHASE) {
                try {
                    $card->account->purchase($item, $price);
                } catch (Exception $e) {
                    Log::warning('Purchase Failed to process', ['e' => $e->__toString()]);
                    PurchaseFailed::dispatch($item, $card->account);
                }
            }

            // -- Tip -- //
            if ($transaction->item_type === PaymentTypeEnum::TIP) {
                try {
                    $card->account->tip($item, $price, ['message' => $user_message ?? '']);
                } catch (Exception $e) {
                    Log::warning('Tip Failed to process', ['e' => $e->__toString()]);
                    TipFailed::dispatch($item, $card->account);
                }
            }

            // -- Subscription -- //
            if ($transaction->item_type === PaymentTypeEnum::SUBSCRIPTION) {
                if (Str::lower($transaction->stage) === StageEnum::INITIAL) {
                    try {
                        $subscription = $card->account->createSubscription($item, $price, [
                            'manual_charge' => false,
                        ]);
                        $subscription->custom_attributes = [ 'segpay_reference' => $transaction->transactionId ];
                        $subscription->process();

                        ItemSubscribed::dispatch($item, $card->account->owner);
                    } catch (Exception $e) {
                        Log::warning('Subscription Failed to be created', ['e' => $e->__toString()]);
                        SubscriptionFailed::dispatch($item, $card->account);
                    }
                } else if (
                    Str::lower($transaction->stage) === StageEnum::REBILL
                    || Str::lower($transaction->stage) === StageEnum::CONVERSION
                ) {
                    $subscription = Subscription::where('custom_attributes->segpay_reference', $transaction->relatedTransactionId)->first();
                    if (isset($subscription)) {
                        $subscription->process();
                    } else {
                        // Can't Find subscription
                    }
                }
            }

        } else if ($transaction->transactionType === TransactionType::CHARGE) {
            // TODO: Chargeback
        } else if ($transaction->transactionType === TransactionType::CREDIT) {
            // TODO: Refund
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