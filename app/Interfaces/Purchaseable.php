<?php
namespace App\Interfaces;

use App\Models\User;
use App\Models\Fanledger;
use App\Enums\PaymentTypeEnum;
use App\Models\Financial\Account;
use App\Models\Financial\Transaction;

interface Purchaseable {

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
     * Purchase this model from Account $from
     * @param Account $from
     * @param int|null $amount
     * @return void
     */
    public function purchase(Account $from,int $amount = null): void;

    /**
     * "un-purchase" this model in the event of a chargeback
     * @param Transaction $transaction
     * @return void
     */
    public function chargeback(Transaction $transaction): void;

    /**
     * Verifies if a price point if valid for purchasing this model
     * @param int $amount
     * @return bool
     */
    public function verifyPrice(int $amount): bool;

}
