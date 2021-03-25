<?php

namespace Tests\Unit\Financial;

use App\Models\Financial\Account;
use App\Models\Financial\Transaction;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\Financial\AccountHelpers;
use App\Jobs\Financial\UpdateAccountBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Financial\Exceptions\TransactionAlreadySettled;

/**
 * Unit Tests for the `App\Models\Financial\Transaction` Model
 *
 * @group unit
 * @group financial
 * @group financial-transaction
 *
 * @package Tests\Unit\Financial
 */
class FinancialTransactionModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test if transactions are created successfully
     */
    public function test_create_transaction()
    {
        Event::fake([UpdateAccountBalance::class]);
        $accounts = AccountHelpers::createInternalAccounts([10000, 0]);
        $transactions = $accounts[0]->moveTo($accounts[1], 1000);

        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'id' => $transactions['debit']->getKey(),
            'account_id' => $accounts[0]->getKey(),
            'credit_amount' => 0,
            'debit_amount' => 1000,
            'settled_at' => null,
        ]);

        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'id' => $transactions['credit']->getKey(),
            'account_id' => $accounts[1]->getKey(),
            'credit_amount' => 1000,
            'debit_amount' => 0,
            'settled_at' => null,
        ]);

        Event::assertDispatched(UpdateAccountBalance::class, 2);
    }

    /**
     * Test if transaction fees settle correctly
     */
    public function test_settle_default_transaction_fees()
    {
        // Setup
        $accounts = AccountHelpers::createInternalAccounts([10000, 0]);

        // Default Fees:
        // PlatformFee => 30% | 300
        // Tax => 5% | 50
        $transactions = $accounts[0]->moveTo($accounts[1], 1000);
        $transaction = $transactions->get('credit');

        $transaction->settleFees();

        $platformFeeAccount = Account::getFeeAccount('platformFee', $this->defaultSystem, $this->defaultCurrency);
        $taxAccount = Account::getFeeAccount('tax', $this->defaultSystem, $this->defaultCurrency);

        // PlatformFee Transaction Set | 1000 * 0.3 = 300
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $accounts[1]->getKey(),
            'debit_amount' => 300,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $platformFeeAccount->getKey(),
            'credit_amount' => 300,
        ]);

        // Tax Transaction Set | 1000 * 0.05 = 50
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $accounts[1]->getKey(),
            'debit_amount' => 50,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $taxAccount->getKey(),
            'credit_amount' => 50,
        ]);

        // Make sure all account fee debit transactions are settled.
        $platformFeeDebitTransaction = Transaction::where('account_id', $accounts[1]->getKey())
            ->where('debit_amount', 300)->first();
        $this->assertIsSettled($platformFeeDebitTransaction);

        $taxDebitTransaction = Transaction::where('account_id', $accounts[1]->getKey())
            ->where('debit_amount', 50)->first();
        $this->assertIsSettled($taxDebitTransaction);
    }

    /**
     * Test if transaction balance settles correctly
     */
    public function test_settle_transaction_balance()
    {
        // Setup
        $inAccount = Account::factory()->asIn()->create();
        $creatorAccount = Account::factory()->asInternal()->create();

        $transactions = $inAccount->moveToInternal(10000);

        $this->assertCurrencyAmountIsEqual(0, $transactions['debit']->calculateBalance());
        $this->assertCurrencyAmountIsEqual(0, $transactions['credit']->calculateBalance());

        $transactions['debit']->settleBalance();
        $transactions['debit']->save();
        $this->assertIsSettled($transactions['debit']);
        $this->assertHasBalanceOf(-10000, $transactions['debit']);

        $transactions['credit']->settleBalance();
        $transactions['credit']->save();
        $this->assertIsSettled($transactions['credit']);
        $this->assertHasBalanceOf(10000, $transactions['credit']);

        // Moving to creators Account
        $userAccount = $inAccount->owner->getInternalAccount($this->defaultSystem, $this->defaultCurrency);

        // Settle Account balance
        $userAccount->settleBalance();

        $transactions = $userAccount->moveTo($creatorAccount, 1000);

        // Prior Balances
        $this->assertCurrencyAmountIsEqual(10000, $transactions['debit']->calculateBalance());
        $this->assertCurrencyAmountIsEqual(0, $transactions['credit']->calculateBalance());

        $transactions['debit']->settleBalance();
        $transactions['debit']->save();
        $this->assertIsSettled($transactions['debit']);
        $this->assertHasBalanceOf(9000, $transactions['debit']);

        $transactions['credit']->settleBalance();
        $transactions['credit']->save();
        $this->assertIsSettled($transactions['credit']);
        $this->assertHasBalanceOf(1000, $transactions['credit']);

        // 2nd Transaction
        $transactions = $userAccount->moveTo($creatorAccount, 1000);

        // Prior Balances
        $this->assertCurrencyAmountIsEqual(9000, $transactions['debit']->calculateBalance());
        $this->assertCurrencyAmountIsEqual(1000, $transactions['credit']->calculateBalance());

        $transactions['debit']->settleBalance();
        $transactions['debit']->save();
        $this->assertIsSettled($transactions['debit']);
        $this->assertHasBalanceOf(8000, $transactions['debit']);

        $transactions['credit']->settleBalance();
        $transactions['credit']->save();
        $this->assertIsSettled($transactions['credit']);
        $this->assertHasBalanceOf(2000, $transactions['credit']);
    }

    /**
     * Settled transactions are final
     */
    public function test_cannot_settle_transaction_multiple_times()
    {
        $inAccount = Account::factory()->asIn()->create();

        $transactions = $inAccount->moveToInternal(10000);

        $this->assertCurrencyAmountIsEqual(0, $transactions['debit']->calculateBalance());
        $this->assertCurrencyAmountIsEqual(0, $transactions['credit']->calculateBalance());

        $transactions['debit']->settleBalance();
        $transactions['debit']->save();
        $this->assertIsSettled($transactions['debit']);
        $this->assertHasBalanceOf(-10000, $transactions['debit']);
        $this->expectException(TransactionAlreadySettled::class);
        $transactions['debit']->settleBalance();
    }

    /**
     * Settled transactions are final
     */
    public function test_attempting_to_modify_settled_transaction_should_fail()
    {
        $inAccount = Account::factory()->asIn()->create();

        $transactions = $inAccount->moveToInternal(10000);

        $this->assertCurrencyAmountIsEqual(0, $transactions['debit']->calculateBalance());
        $this->assertCurrencyAmountIsEqual(0, $transactions['credit']->calculateBalance());

        $transactions['debit']->settleBalance();
        $transactions['debit']->save();
        $this->assertIsSettled($transactions['debit']);
        $this->assertHasBalanceOf(-10000, $transactions['debit']);

        $transactions['credit']->settleBalance();
        $transactions['credit']->save();
        $this->assertIsSettled($transactions['credit']);
        $this->assertHasBalanceOf(10000, $transactions['credit']);
        $transactions['credit']->balance = 0;
        $this->expectException(TransactionAlreadySettled::class);
        $transactions['credit']->save();
    }

    /**
     *
     */
    public function test_fail_transaction_gracefully()
    {
        // TODO: Implement
        $this->markTestIncomplete();
    }
}
