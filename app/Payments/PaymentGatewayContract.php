<?php

namespace App\Payments;

use Money\Money;
use App\Models\Tip;
use App\Interfaces\Tippable;
use App\Interfaces\Purchaseable;
use App\Interfaces\Subscribable;
use App\Models\Campaign;
use App\Models\Financial\Account;

interface PaymentGatewayContract
{
    public function purchase(Account $account, Purchaseable $item, Money $price);

    public function tip(Account $account, Tip $tip, Money $price);

    public function subscribe(Account $account, Subscribable $item, Money $price, Campaign $campaign = null);
}