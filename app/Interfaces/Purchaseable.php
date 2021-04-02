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
interface Purchaseable extends PaymentSendable, Shareable
{
    /**
     * @param  string  $type - PaymentTypeEnum
     */
    public function receivePayment(
        string $fltype,
        User $sender,
        int $amountInCents,
        array $customAttributes = []
    ) : ?Fanledger;

    /**
     * Verifies if a price point if valid for purchasing this model
     *
     * @param int|Money $amount
     * @return bool
     */
    public function verifyPrice($amount): bool;

    /**
     * Format money for current local and currency
     *
     * @param Money $money
     * @return string
     */
    public static function formatMoney(Money $money): string;

    /**
     * Format money to decimal for current local
     * @param Money $money
     * @return string
     */
    public static function formatMoneyDecimal(Money $money): string;

}
