<?php

namespace App\Payments;

use Exception;
use Money\Money;
use App\Models\Tip;
use App\Models\Campaign;
use App\Events\TipFailed;
use App\Events\ItemSubscribed;
use App\Events\PurchaseFailed;
use Illuminate\Support\Carbon;
use App\Enums\CampaignTypeEnum;
use App\Interfaces\Purchaseable;
use App\Interfaces\Subscribable;
use App\Models\Financial\Account;
use App\Events\SubscriptionFailed;
use App\Http\Resources\Chatmessage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Enums\ShareableAccessLevelEnum;
use App\Models\Financial\Exceptions\Account\IncorrectTypeException;
use App\Models\Financial\Exceptions\InvalidFinancialSystemException;

/**
 * Payment Gateway for faking transactions. Only valid in dev and test environments
 * @package App\Payments
 */
class FakedPaymentGateway implements PaymentGatewayContract
{
    /**
     * Handle fake purchase
     *
     * @param Account $account
     * @param Purchaseable $item
     * @param Money $price
     * @return array
     */
    public function purchase(Account $account, Purchaseable $item, Money $price)
    {
        $this->validateAccount($account);

        try {
            $transactions = $account->purchase($item, $price);
        } catch (Exception $e) {
            Log::warning('Purchase Failed to process', ['e' => $e->__toString()]);
            PurchaseFailed::dispatch($item, $account);
            return [
                'success' => false,
                'message' => 'Failed to process purchase',
            ];
        }

        // TODO: Add additional checks for instant access
        $instantAccess = true;
        if ($instantAccess) {
            $item->grantAccessFor($account->getOwner(), ShareableAccessLevelEnum::PREMIUM);
        }

        return [
            'success' => true,
            'item' => $item,
            'instantAccess' => $instantAccess,
            'faked' => true,
            'processed_at' => Carbon::now(),
        ];
    }

    /**
     * Handle a faked tip
     *
     * @param Account $account
     * @param Tip $item
     * @param Money $price
     * @return array
     */
    public function tip(Account $account, Tip $tip, Money $price)
    {
        $this->validateAccount($account);

        try {
            $transactions = $tip->process(true, ['account_id' => $account->id]);
        } catch (Exception $e) {
            Log::warning('Tip Failed to process', ['e' => $e->__toString()]);
            TipFailed::dispatch($tip, $account);
            return [
                'success' => false,
                'message' => 'Failed to process tip',
            ];
        }

        return [
            'success' => true,
            'tip' => $tip,
            'message' => isset($transactions['message']) ? new Chatmessage($transactions['message']) : null,
            'faked' => true,
            'processed_at' => Carbon::now(),
        ];
    }

    /**
     * Complete a faked Subscription
     *
     * @param Account $account
     * @param Subscribable $item
     * @param Money $price
     * @return array
     */
    public function subscribe(Account $account, Subscribable $item, Money $price, Campaign $campaign = null)
    {
        $this->validateAccount($account);

        // Create subscription
        $subscription = $account->createSubscription($item, $price, [
            'manual_charge' => false,
        ]);

        // Apply campaign setting if there is one
        if (isset($campaign)) {
            $subscription->applyCampaign($campaign);
        }

        try {
            $transactions = $subscription->process();
            ItemSubscribed::dispatch($item, $account->owner);
        } catch (Exception $e) {
            Log::warning('Subscription Failed to be created', ['e' => $e->__toString()]);
            SubscriptionFailed::dispatch($item, $account);
            return [
                'success' => false,
                'message' => 'Failed to process subscription',
            ];
        }

        // TODO: Add additional checks for instant access
        $instantAccess = true;

        if ($instantAccess) {
            $item->grantAccessFor($account->getOwner(), ShareableAccessLevelEnum::PREMIUM);
        }

        return [
            'success' => true,
            'item' => $item,
            'instantAccess' => $instantAccess,
            'faked' => true,
            'processed_at' => Carbon::now(),
        ];
    }

    /**
     * Validates this is the correct financial system an type of account
     *
     * @param Account $account
     * @return void
     * @throws InvalidFinancialSystemException
     * @throws IncorrectTypeException
     */
    private function validateAccount(Account $account)
    {
        if (App::environment() === 'production') {
            throw new InvalidFinancialSystemException('Faked', $account);
        }
    }
}
