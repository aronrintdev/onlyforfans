<?php

namespace Tests\Helpers\Financial;

use App\Models\Financial\Account;
use Illuminate\Support\Collection;

/**
 * Helper functions for generating random accounts and transactions for those accounts.
 * @package Tests\Helpers\Financial
 */
class AccountHelpers
{
    /**
     * Starts a new transaction builder
     *
     * @return TransactionsBuilder
     */
    public static function generateTransactions(): TransactionsBuilder
    {
        return new TransactionsBuilder();
    }

    /**
     * Create Collection of Internal accounts
     *
     * @param array $balances
     * @return Collection
     */
    public static function createInternalAccounts($balances = []): Collection
    {
        $accounts = new Collection([]);
        foreach ($balances as $balance) {
            $accounts = $accounts->merge([ Account::factory()->asInternal()->withBalance($balance)->create() ]);
        }
        return $accounts;
    }

    /**
     * Loads a users wallet with funds
     *
     * @param int $amount
     * @param mixed|null $in
     * @return Collection `[ 'in', 'internal', 'transactions' ]`
     */
    public static function loadWallet(int $amount, $in = null): Collection
    {
        if (!isset($in)) {
            $in = Account::factory()->asIn()->create();
        }
        $wallet = $in->owner->getWalletAccount($in->system, $in->currency);
        $transactions = $in->moveToWallet($amount);
        AccountHelpers::settleAccounts([$in, $wallet]);
        return new Collection([ 'in' => $in, 'internal' => $wallet, 'transactions' => $transactions ]);
    }

    /**
     * Settle and save account given
     *
     * @param array|Collection $accounts
     * @return void
     */
    public static function settleAccounts($accounts = [])
    {
        foreach($accounts as $account) {
            $account->settleBalance();
            $account->save();
        }
    }
}
