<?php

namespace App\Payouts;

use App\Models\Financial\Account;
use Money\Money;

interface PayoutGatewayContract
{
    /**
     * Request a payout be made
     *
     * @param Account  $from    Account being taken from
     * @param Account  $to      Account being payed out to
     * @param Money    $amount  Amount being payed out
     * @return mixed
     */
    public function request(Account $from, Account $to, Money $amount);
}