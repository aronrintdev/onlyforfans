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

    public function grantAccess(User $user, string $accessLevel, $cattrs = [], $meta = []): void;
    public function revokeAccess(User $user, $cattrs = [], $meta = []): void;

    public function getOwnerAccount(string $system, string $currency): Account;

    /**
     * Verifies if a price point if valid for purchasing this model
     * @param int|Money $amount
     * @return bool
     */
    public function verifyPrice($amount): bool;


    public function getKey();
    public function getMorphString(): string;
    public function getDescriptionNameString(): string;
}
