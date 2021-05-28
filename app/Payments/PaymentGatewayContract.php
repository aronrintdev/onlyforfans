<?php

namespace App\Payments;

use Money\Money;
use App\Interfaces\Tippable;
use App\Interfaces\Purchaseable;
use App\Interfaces\Subscribable;
use App\Models\Financial\Account;

interface PaymentGatewayContract
{
    public function purchase(Account $account, Purchaseable $item, Money $price);

    public function tip(Account $account, Tippable $item, Money $price);

    public function subscribe(Account $account, Subscribable $item, Money $price);
}