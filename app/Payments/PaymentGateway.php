<?php

namespace App\Payments;

use Money\Money;
use App\Models\Tip;
use App\Interfaces\Purchaseable;
use App\Interfaces\Subscribable;
use App\Models\Financial\Account;
use App\Models\Financial\Exceptions\InvalidFinancialSystemException;

/**
 * Payment Gateway switcher class
 * @package App\Payments
 */
class PaymentGateway implements PaymentGatewayContract
{

    public function purchase(Account $account, Purchaseable $item, Money $price)
    {
        switch($account->system) {
            case 'segpay':
                return (new SegpayPaymentGateway)->purchase($account, $item, $price);
            default:
                throw new InvalidFinancialSystemException($account->system, $account);
        }
    }

    public function tip(Account $account, Tip $tip, Money $price)
    {
        switch ($account->system) {
            case 'segpay':
                return (new SegpayPaymentGateway)->tip($account, $tip, $price);
            default:
                throw new InvalidFinancialSystemException($account->system, $account);
        }
    }

    public function subscribe(Account $account, Subscribable $item, Money $price)
    {
        switch ($account->system) {
            case 'segpay':
                return (new SegpayPaymentGateway)->subscribe($account, $item, $price);
            default:
                throw new InvalidFinancialSystemException($account->system, $account);
        }
    }

}
