<?php
namespace App\Interfaces;

use App\Models\User;
use App\Enums\PaymentTypeEnum;
use App\Models\Fanledger;

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

}
