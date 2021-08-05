<?php
namespace App\Interfaces;

use Money\Money;
use App\Models\User;

/**
 * A Purchaseable Item
 *
 * @param Money $price
 *
 * @package App\Interfaces
 */
interface Purchaseable extends PaymentSendable, Shareable, CanFormatMoney
{
    /**
     * Verifies if a price point if valid for purchasing this model
     *
     * @param int|Money $amount
     * @param string $currency
     * @return bool
     */
    public function verifyPrice($amount, $currency = 'USD'): bool;

}
