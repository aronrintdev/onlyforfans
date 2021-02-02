<?php
namespace App\Interfaces;

use App\User;
use App\Enums\PaymentTypeEnum;
use App\Fanledger;

interface Purchaseable {

    public function receivePayment(
        string $ptype, // PaymentTypeEnum
        User $sender,
        int $amountInCents,
        array $cattrs = []
    ) : ?Fanledger;

}
