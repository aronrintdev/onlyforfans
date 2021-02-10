<?php
namespace App\Interfaces;

use App\Models\User;
use App\Enums\PaymentTypeEnum;
use App\Models\FanLedger;

interface Purchaseable {

    /**
     * @param  string  $type - PaymentTypeEnum
     */
    public function receivePayment(
        string $type,
        User $sender,
        int $amountInCents,
        array $customAttributes = []
    ) : ?FanLedger;

}
