<?php

namespace App\Payments;

use Money\Money;
use App\Models\Tip;
use App\Enums\PaymentTypeEnum;
use Illuminate\Support\Carbon;
use App\Jobs\FakeSegpayPayment;
use App\Interfaces\Purchaseable;
use App\Interfaces\Subscribable;
use App\Models\Financial\Account;
use Illuminate\Support\Facades\App;
use App\Models\Financial\SegpayCall;
use Illuminate\Support\Facades\Config;
use App\Enums\Financial\AccountTypeEnum;
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
            return $this->fakePurchase($account, $item, $price);
        }

        $segpayCall = SegpayCall::confirmPurchase($account, $price, $item);

        return [
            'success' => isset($segpayCall->failed_at) ? false : true,
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
            return $this->fakeTip($account, $tip, $price);
        }

        $segpayCall = SegpayCall::confirmTip($account, $price, $tip);

        return [
            'success' => isset($segpayCall->failed_at) ? false : true,
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
    public function subscribe(Account $account, Subscribable $item, Money $price)
    {
        $this->validateAccount($account);
        if (Config::get('segpay.fake') && App::environment() != 'production') {
            return $this->fakeSubscribe($account, $item, $price);
        }

        // Create subscription
        $subscription = $account->createSubscription($item, $price, [
            'manual_charge' => false,
        ]);

        // Send Segpay One click call
        $segpayCall = SegpayCall::confirmSubscription($account, $price, $subscription);

        return [
            'success' => isset($segpayCall->failed_at) ? false : true,
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

    /**
     * Fake a segpay purchase
     *
     * @param Account $account
     * @param Purchaseable $item
     * @param Money $price
     * @return array
     */
    private function fakePurchase(Account $account, Purchaseable $item, Money $price)
    {
        // Dispatch Faked Event
        FakeSegpayPayment::dispatch($item, $account, PaymentTypeEnum::PURCHASE, $price);
        return [
            'success' => true,
            'faked' => true,
            'processed_at' => Carbon::now(),
        ];
    }

    /**
     * Fake a segpay Tip
     *
     * @param Account $account
     * @param Tippable $item
     * @param Money $price
     * @return array
     */
    private function fakeTip(Account $account, Tip $tip, Money $price)
    {
        // Dispatch Faked Event
        FakeSegpayPayment::dispatch($tip, $account, PaymentTypeEnum::TIP, $price);
        return [
            'success' => true,
            'faked' => true,
            'processed_at' => Carbon::now(),
        ];
    }

    /**
     * Fake a segpay Subscription
     *
     * @param Account $account
     * @param Subscribable $item
     * @param Money $price
     * @return array
     */
    private function fakeSubscribe(Account $account, Subscribable $item, Money $price)
    {
        // Dispatch Faked Event
        FakeSegpayPayment::dispatch($item, $account, PaymentTypeEnum::SUBSCRIPTION, $price);
        return [
            'success' => true,
            'faked' => true,
            'processed_at' => Carbon::now(),
        ];
    }



}