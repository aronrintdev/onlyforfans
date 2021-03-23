<?php

namespace Tests\Asserts\Financial;

use App\Models\Financial\Transaction;

trait FinancialAsserts
{
    /**
     * Checks if Transaction has been settled
     *
     * @param  Transaction  $transaction
     * @param  bool  $lazy - set to true to only check settled on model instance
     */
    protected function assertIsSettled(Transaction $transaction, bool $lazy = false)
    {
        $this->assertThat($transaction, new IsSettled());
    }

    /**
     * Checks if Transaction has not been settled
     *
     * @param  Transaction  $transaction
     * @param  bool  $lazy - set to true to only check settled on model instance
     */
    protected function assertIsNotSettled(Transaction $transaction, bool $lazy = false)
    {
        $this->assertThat($transaction, new IsNotSettled());
    }
}