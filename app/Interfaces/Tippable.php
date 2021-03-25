<?php

namespace App\Interfaces;

use App\Models\Financial\Account;
use App\Models\Financial\Transaction;

interface Tippable
{
    /**
     * Tip this Tippable model from Account $from
     *
     * @param Account $from
     * @param int $amount
     * @return void
     */
    public function tip(Account $from, int $amount): void;

    /**
     * Chargeback a tip made to this model.
     * @param Transaction $transaction
     * @return void
     */
    public function tipChargeback(Transaction $transaction): void;
}