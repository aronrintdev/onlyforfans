<?php

namespace Tests\Asserts\Financial;

use App\Models\Financial\Account;
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

    /**
     * Checks Account or Transaction balance amount
     *
     * @param int|Money $amount
     * @param Account|Transaction $model
     * @param string $message
     * @return void
     */
    protected function assertHasBalanceOf($amount, $model, string $message = '')
    {
        $this->assertThat($model, new BalanceEquals($amount), $message);
    }

    /**
     * Checks if account can make transactions
     * @param Account $account
     * @param bool $lazy
     * @param string $message
     * @return void
     */
    protected function assertCanMakeTransactions(Account $account, $lazy = false, string $message = '')
    {
        $this->assertThat($account, new CanMakeTransactions($lazy), $message);
    }

    /**
     * Checks if account can not make transactions
     * @param Account $account
     * @param bool $lazy
     * @param string $message
     * @return void
     */
    protected function assertCanNotMakeTransactions(Account $account, $lazy = false, string $message = '')
    {
        $this->assertThat($account, new CanNotMakeTransactions($lazy), $message);
    }

}