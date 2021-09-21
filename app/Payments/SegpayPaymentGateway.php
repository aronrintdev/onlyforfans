<?php

namespace App\Payments;

use App\Enums\CampaignTypeEnum;
use Money\Money;
use App\Models\Tip;
use App\Models\Campaign;
use App\Interfaces\Purchaseable;
use App\Interfaces\Subscribable;
use App\Models\Financial\Account;
use Illuminate\Support\Facades\App;
use App\Models\Financial\SegpayCall;
use Illuminate\Support\Facades\Config;
use App\Enums\ShareableAccessLevelEnum;
use App\Enums\Financial\AccountTypeEnum;
use App\Models\Financial\Exceptions\AlreadyProcessingException;
use App\Models\Financial\Exceptions\Account\IncorrectTypeException;
use App\Models\Financial\Exceptions\InvalidFinancialSystemException;

/**
 *
 * @package App\Payments
 */
class SegpayPaymentGateway implements PaymentGatewayContract
{

    protected $system = 'segpay';

    /**
     * Handle purchase with known Segpay card account
     *
     * @param Account $account
     * @param Purchaseable $item
     * @param Money $price
     * @return array
     */
    public function purchase(Account $account, Purchaseable $item, Money $price)
    {
        $this->validateAccount($account);
        if (Config::get('segpay.fake') && App::environment() != 'production') {
            return (new FakedPaymentGateway())->purchase($account, $item, $price);
        }

        try {
            $segpayCall = SegpayCall::confirmPurchase($account, $price, $item);
        } catch (AlreadyProcessingException $e) {
            return [
                'success' => false,
                'message' => 'Already Processing Payment',
            ];
        }

        // TODO: Add additional checks for instant access
        $instantAccess = isset($segpayCall->failed_at) ? false : true;

        if ($instantAccess) {
            $item->grantAccessFor($account->getOwner(), ShareableAccessLevelEnum::PREMIUM);
        }

        return [
            'success' => isset($segpayCall->failed_at) ? false : true,
            'item' => $item,
            'instantAccess' => $instantAccess,
            'processed_at' => $segpayCall->processed_at,
        ];
    }

    /**
     * Handle a tip with a known Segpay card account
     *
     * @param Account $account
     * @param Tip $item
     * @param Money $price
     * @return array
     */
    public function tip(Account $account, Tip $tip, Money $price)
    {
        $this->validateAccount($account);
        if (Config::get('segpay.fake') && App::environment() != 'production') {
            return(new FakedPaymentGateway())->tip($account, $tip, $price);;
        }

        try {
            $segpayCall = SegpayCall::confirmTip($account, $price, $tip);
        } catch (AlreadyProcessingException $e) {
            return [
                'success' => false,
                'message' => 'Already Processing Payment',
            ];
        }

        return [
            'success' => isset($segpayCall->failed_at) ? false : true,
            'tip' => $tip,
            'processed_at' => $segpayCall->processed_at,
        ];
    }

    /**
     * Complete a Segpay Subscription with a known Segpay card account
     *
     * @param Account $account
     * @param Subscribable $item
     * @param Money $price
     * @return array
     */
    public function subscribe(Account $account, Subscribable $item, Money $price, Campaign $campaign = null)
    {
        $this->validateAccount($account);
        if (Config::get('segpay.fake') && App::environment() != 'production') {
            return (new FakedPaymentGateway())->subscribe($account, $item, $price, $campaign);
        }

        // Create subscription
        $subscription = $account->createSubscription($item, $price, [
            'manual_charge' => false,
        ]);

        // Apply campaign setting if there is one
        if (isset($campaign)) {
            if ($campaign->type === CampaignTypeEnum::DISCOUNT) {
                $subscription->initial_period = 'daily';
                $subscription->initial_period_interval = 30;
                $subscription->initial_price = $campaign->getDiscountPrice($price)->getAmount();
                $subscription->save();
            }
            if ($campaign->type === CampaignTypeEnum::TRIAL) {
                $subscription->initial_period = 'daily';
                $subscription->initial_period_interval = $campaign->trial_days;
                $subscription->initial_price = 0;
                $subscription->save();
            }
        }

        // Send Segpay One click call
        try {
            $segpayCall = SegpayCall::confirmSubscription($account, $price, $subscription);
        } catch (AlreadyProcessingException $e) {
            return [
                'success' => false,
                'item' => $item,
                'message' => 'Already Processing Payment',
            ];
        }

        // TODO: Add additional checks for instant access
        $instantAccess = isset($segpayCall->failed_at) ? false : true;

        if ($instantAccess) {
            $item->grantAccessFor($account->getOwner(), ShareableAccessLevelEnum::PREMIUM);
        }

        return [
            'success' => isset($segpayCall->failed_at) ? false : true,
            'instantAccess' => $instantAccess,
            'processed_at' => $segpayCall->processed_at,
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
        if ($account->system !== $this->system) {
            throw new InvalidFinancialSystemException($this->system, $account);
        }
        if ($account->type !== AccountTypeEnum::IN) {
            throw new IncorrectTypeException($account, AccountTypeEnum::IN);
        }
    }

}
