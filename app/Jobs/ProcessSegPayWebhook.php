<?php

namespace App\Jobs;

use Exception;
use Throwable;
use App\Models\Tip;
use App\Models\User;

use App\Models\Webhook;
use App\Events\TipFailed;

use App\Helpers\Tippable;
use Illuminate\Support\Str;
use App\Helpers\Purchasable;
use App\Models\Subscription;
use App\Helpers\Subscribable;

use Illuminate\Bus\Queueable;
use App\Enums\PaymentTypeEnum;
use App\Events\ItemSubscribed;
use App\Events\PurchaseFailed;
use App\Models\Financial\Flag;
use Illuminate\Support\Carbon;
use App\Apis\Segpay\Transaction;
use App\Apis\Segpay\Enums\Action;
use App\Events\PaymentMethodAdded;
use App\Events\SubscriptionFailed;

use App\Models\Traits\FormatMoney;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Models\Financial\SegpayCall;
use App\Models\Financial\SegpayCard;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use App\Enums\ShareableAccessLevelEnum;
use Illuminate\Queue\InteractsWithQueue;
use App\Apis\Segpay\Enums\TransactionType;
use App\Enums\WebhookStatusEnum as Status;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Apis\Segpay\Enums\Stage as StageEnum;
use Illuminate\Foundation\Events\Dispatchable;
use App\Models\Financial\Transaction as FinancialTransaction;

/**
 * Processes a webhook that was received from Segpay
 *
 * @package App\Jobs
 */
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
            $notes = null;
            try {
                $transaction = new Transaction($webhook->body);
                switch (Str::lower($transaction->action)) {
                    /** Inquiry */
                    case Action::PROBE:
                        $notes = $this->handleProbe($transaction);
                        break;
                    /** Access Enable */
                    case Action::ENABLE:
                        $notes = $this->handleEnable($transaction);
                        break;
                    /** Access Disable */
                    case Action::DISABLE:
                        $notes = $this->handleDisable($transaction);
                        break;
                    /** Cancellation */
                    case Action::CANCEL:
                        $notes = $this->handleCancel($transaction);
                        break;
                    /** Reactivation */
                    case Action::REACTIVATION:
                        $notes = $this->handleReactivation($transaction);
                        break;
                    /** Transaction */
                    case Action::AUTH:
                        $notes = $this->handleAuth($transaction);
                        break;
                    case Action::VOID:
                        $notes = $this->handleVoid($transaction);
                        break;
                }
            } catch (Exception $e) {
                $webhook->status = Status::ERROR;
                $webhook->handled_at = Carbon::now();
                $webhook->notes = 'Error on execution: ' . $e->getMessage();
                $webhook->save();
                Log::error('Error on ProcessSegPayWebhook', [ 'message' => $e->getMessage(), 'stacktrace' => $e->getTraceAsString() ]);
                Flag::raise($this->webhook, ['description' => 'Segpay Webhook Failed to Process']);
                return;
            }
            if (isset($notes)) {
                $webhook->notes = ['ProcessSegPayWebhook' => $notes];
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
        // We do not use this webhook
        return ['message' => 'Probe hook is not handle by this system.'];
    }

    /**
     * Handle action: `enable` hook
     *
     * *From Documentation:*
     * > Access to your system has been granted, following a purchase.
     */
    private function handleEnable($transaction)
    {
        // We do not use this webhook
        return ['message' => 'Enable hook is not handle by this system.'];
    }

    /**
     * Handle action: `disable` hook
     *
     * *From Documentation:*
     * > Access to your system has been removed, following an account cancellation/expiration.
     */
    private function handleDisable($transaction)
    {
        // We do not use this webhook
        return ['message' => 'Disable hook is not handle by this system.'];
    }

    /**
     * Handle action `cancel` hook
     *
     * *From Documentation:*
     * > A member has requested a cancellation or a refund/cancellation.
     */
    private function handleCancel($transaction)
    {
        $subscription = Subscription::where('custom_attributes->segpay_reference', $transaction->relatedTransactionId)
            ->first();
        if (isset($subscription)) {
            $subscription->cancel();
            return ['message' => 'Subscription canceled'];
        } else {
            // Can't Find subscription
            $message = 'Failed to cancel subscription, could not fine subscription segpay_reference';
            Flag::raise($this->webhook, ['description' => $message ]);
            return ['message' => $message];
        }
    }

    /**
     * Handle action `reactivation` hook
     *
     * *From Documentation:*
     * > A cancelled or expired subscription has been reactivated.
     */
    private function handleReactivation($transaction)
    {
        $subscription = Subscription::where('custom_attributes->segpay_reference', $transaction->relatedTransactionId)
            ->first();
        if (isset($subscription)) {
            $subscription->reactivate();
            return ['message' => 'Subscription reactivated'];
        } else {
            // Can't Find subscription
            $message ='Failed to cancel subscription, could not fine subscription segpay_reference';
            Flag::raise($this->webhook, ['description' => $message]);
            return ['message' => $message];
        }
    }

    /**
     * Handle action: `auth` hook
     *
     * *From Documentation:*
     * > An authorization has occurred.
     */
    private function handleAuth($transaction)
    {



        // ---------------------------------- SALE ---------------------------------- //
        if (Str::lower($transaction->transactionType) === TransactionType::SALE) {
            // Check for reference_id
            $item = $this->getItem($transaction);

            // Handle Failed Transactions
            if (Str::lower($transaction->approved) === 'no') {
                $account = User::find($transaction->user_id)->getInternalAccount('segpay', $transaction->currencyCode);
                if ($transaction->item_type === PaymentTypeEnum::PURCHASE) {
                    PurchaseFailed::dispatch($item, $account, 'Not Approved');
                    return ['message' => 'Purchase not approved'];
                }
                if ($transaction->item_type === PaymentTypeEnum::TIP) {
                    TipFailed::dispatch($item, $account, 'Not Approved');
                    return ['message' => 'Purchase not approved'];
                }
                if ($transaction->item_type === PaymentTypeEnum::SUBSCRIPTION) {
                    SubscriptionFailed::dispatch($item, $account, 'Not Approved');
                    return ['message' => 'Purchase not approved'];
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
                PaymentMethodAdded::dispatch($card->account, $card->getOwner()->first());
            }

            // Translate decimal price from segpay back to money type
            $price = $this->parseMoneyDecimal($transaction->price, $transaction->currencyCode);

            // -------------------------------- PURCHASE -------------------------------- //
            if ($transaction->item_type === PaymentTypeEnum::PURCHASE) {
                try {
                    $transactions = $card->account->purchase($item, $price);
                    $transactions['inTransactions']['debit']->metadata = [ 'segpay_transaction_id' => $transaction->transactionId ];
                    $transactions['inTransactions']['debit']->save();
                } catch (Exception $e) {
                    Log::warning('Purchase Failed to process', ['e' => $e->__toString()]);
                    PurchaseFailed::dispatch($item, $card->account);
                    return [ 'message' => 'Failed to Process Purchase' ];
                }
            }

            // ----------------------------------- TIP ---------------------------------- //
            if ($transaction->item_type === PaymentTypeEnum::TIP) {
                try {
                    $transactions = $item->process(true, ['account_id' => $card->account->id]);
                    // $transactions = $card->account->tip($item, $price, ['message' => $user_message ?? '']);
                    $transactions['inTransactions']['debit']->metadata = ['segpay_transaction_id' => $transaction->transactionId];
                    $transactions['inTransactions']['debit']->save();
                } catch (Exception $e) {
                    Log::warning('Tip Failed to process', ['e' => $e->__toString()]);
                    TipFailed::dispatch($item, $card->account);
                    return [ 'message' => 'Failed to Process Tip' ];
                }
            }

            // ------------------------------ SUBSCRIPTION ------------------------------ //
            if ($transaction->item_type === PaymentTypeEnum::SUBSCRIPTION) {
                if (Str::lower($transaction->stage) === StageEnum::INITIAL) {
                    try {
                        $subscription = $card->account->createSubscription($item, $price, [
                            'manual_charge' => false,
                        ]);
                        $subscription->custom_attributes = [ 'segpay_reference' => $transaction->transactionId, 'segpay_purchase_id' => $transaction->purchaseId ];
                        $transactions = $subscription->process();
                        if ($transactions->has('inTransactions')) {
                            $transactions['inTransactions']['debit']->metadata = ['segpay_transaction_id' => $transaction->transactionId];
                            $transactions['inTransactions']['debit']->save();
                        }

                        ItemSubscribed::dispatch($item, $card->account->owner);
                        return ['message' => 'Subscription Initial transaction processed'];
                    } catch (Exception $e) {
                        Log::warning('Subscription Failed to be created', ['e' => $e->__toString()]);
                        SubscriptionFailed::dispatch($item, $card->account);
                        return [ 'message' => 'Failed to Process subscription' ];
                    }
                } else if (
                    Str::lower($transaction->stage) === StageEnum::REBILL
                    || Str::lower($transaction->stage) === StageEnum::CONVERSION
                ) {
                    $subscription = Subscription::where('custom_attributes->segpay_reference', $transaction->relatedTransactionId)->first();
                    if (isset($subscription)) {
                        $transactions = $subscription->process();
                        if ($transactions->has('inTransactions')) {
                            $transactions['inTransactions']['debit']->metadata = ['segpay_transaction_id' => $transaction->transactionId];
                            $transactions['inTransactions']['debit']->save();
                        }

                        return ['message' => 'Subscription rebill processed'];
                    } else {
                        // Can't Find subscription
                        $message = 'Failed to Process subscription Rebill, could not fine subscription segpay_reference';
                        Flag::raise($this->webhook, ['descriptions' => $message]);
                        return ['message' => $message];
                    }
                }
            }

        // ------------------------------- CHARGEBACK ------------------------------- //
        } else if ($transaction->transactionType === TransactionType::CHARGE) {
            // Find original Trans in out system for this transaction
            $transaction = FinancialTransaction::where('metadata->segpay_transaction_id', $transaction->transactionId)
                ->with(['account'])
                ->first();
            if (isset($transaction) === false) {
                $message = 'Failed to find original transaction for chargeback';
                Flag::raise($this->webhook, ['description' => $message]);
                return ['message' => $message];
            }
            $transaction->account->handleChargeback($transaction);
            return ['message' => 'Chargeback handled'];

        // --------------------------------- REFUND --------------------------------- //
        } else if ($transaction->transactionType === TransactionType::CREDIT) {
            // Find original Trans in our system for this transaction
            $transaction = FinancialTransaction::where('metadata->segpay_transaction_id', $transaction->transactionId)
            ->with(['account'])
            ->first();
            if (isset($transaction) === false) {
                $message = 'Failed to find original transaction for refund';
                Flag::raise($this->webhook, ['description' => $message]);
                return ['message' => $message];
            }

            // TODO: Need Account Function to handle refund
            $message = 'Refund is not implemented';
            Flag::raise($this->webhook, ['description' => $message]);
            return ['message' => $message];
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
        if (Str::lower($transaction->transactionType) === TransactionType::SALE) {
            $item = $this->getItem($transaction);
        }

        if (isset($transaction->octoken)) {
            $card = SegpayCard::findByToken($transaction->octoken);
            $account = $card->account;
        } else {
            $user = User::find($transaction->user_id);
            $account = $user->getInternalAccount('segpay', $transaction->currencyCode ?? Config::get('transactions.defaultCurrency'));
        }

        if ($transaction->item_type === PaymentTypeEnum::PURCHASE) {
            PurchaseFailed::dispatch($item, $account, 'Transaction Void');
            return ['message' => 'Purchase Failed on void transaction'];
        }

        if ($transaction->item_type === PaymentTypeEnum::TIP) {
            TipFailed::dispatch($item, $account, 'Transaction Void');
            return ['message' => 'Tip Failed on void transaction'];
        }

        if ($transaction->item_type === PaymentTypeEnum::SUBSCRIPTION) {
            if (Str::lower($transaction->stage) === StageEnum::INITIAL) {
                SubscriptionFailed::dispatch($item, $account, 'Transaction Void');
                return ['message' => 'Subscription Failed on void transaction'];
            } else if (
                Str::lower($transaction->stage) === StageEnum::REBILL
                || Str::lower($transaction->stage) === StageEnum::CONVERSION
            ) {
                $subscription = Subscription::where('custom_attributes->segpay_reference', $transaction->relatedTransactionId)->first();
                if (isset($subscription)) {
                    $subscription->cancel();
                    return ['message' => 'Subscription canceled due to rebill transaction void'];
                } else {
                    // Can't Find subscription
                    $message = 'Failed to cancel subscription on rebill transaction void cancel, could not fine subscription segpay_reference';
                    Flag::raise($this->webhook, ['description' => $message]);
                    return [ 'message' => $message];
                }
            }
        }
    }

    /**
     * Get related item from transaction
     *
     * @param mixed $transaction
     * @return mixed item related to transaction
     */
    private function getItem($transaction)
    {
        // Check for reference_id
        if (isset($transaction->reference_id)) {
            $segpayCall = SegpayCall::find($transaction->reference_id);
            return $segpayCall->resource;
        } else {
            if ($transaction->item_type === PaymentTypeEnum::PURCHASE) {
                return Purchasable::getPurchasableItem($transaction->item_id);
            } else if ($transaction->item_type === PaymentTypeEnum::TIP) {
                return Tip::find($transaction->item_id);
                // return Tippable::getTippableItem($transaction->item_id);
            } else if ($transaction->item_type === PaymentTypeEnum::SUBSCRIPTION) {
                return Subscribable::getSubscribableItem($transaction->item_id);
            }
        }
    }

}