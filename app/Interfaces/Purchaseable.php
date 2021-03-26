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
     * Grants access to this resource for a user
     *
     * @param User $user
     * @param string $accessLevel
     * @param array $cattrs  Custom attributes
     * @param array $meta  Metadata
     * @return void
     */
    public function grantAccess(User $user, string $accessLevel, $cattrs = [], $meta = []): void;

    /**
     * Revokes access to this resource for a user
     *
     * @param User $user
     * @param array $cattrs  Custom attributes
     * @param array $meta  Metadata
     * @return void
     */
    public function revokeAccess(User $user, $cattrs = [], $meta = []): void;

    /**
     * Gets the financial account that purchase transactions will go to
     *
     * @param string $system  Financial System
     * @param string $currency  Currency being used for transaction
     * @return Account
     */
    public function getOwnerAccount(string $system, string $currency): Account;

    /**
     * Verifies if a price point if valid for purchasing this model
     *
     * @param int|Money $amount
     * @return bool
     */
    public function verifyPrice($amount): bool;

    /**
     * Eloquent Model Method, This is so intellisense works with the interface.
     * Gets model primary key.
     */
    public function getKey();

    /**
     * Model Method, this is so intellisense works with interface.
     * Gets the Morph string for model.
     */
    public function getMorphString(): string;

    /**
     * The string used in the transaction description.
     * e.i. "Purchase of `{This methods return}`"
     *
     * @return string
     */
    public function getDescriptionNameString(): string;

}
