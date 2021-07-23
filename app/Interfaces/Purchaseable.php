<?php
namespace App\Interfaces;

use Money\Money;
use App\Models\User;
use App\Models\Fanledger;

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
     * @deprecated
     * @param  string  $type - PaymentTypeEnum
     */
    // public function receivePayment(
    //     string $fltype,
    //     User $sender,
    //     int $amountInCents,
    //     array $customAttributes = []
    // ) : ?Fanledger;

    /**
     * Verifies if a price point if valid for purchasing this model
     *
     * @param int|Money $amount
     * @param string $currency
     * @return bool
     */
    public function verifyPrice($amount, $currency = 'USD'): bool;

}
