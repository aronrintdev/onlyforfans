<?php

namespace Tests\Unit\Financial;

use App\Models\Financial\Account;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Financial\Exceptions\TransactionAlreadySettled;

/**
 * Unit Tests for the `App\Models\Financial\Transaction` Model
 *
 * @group unit
 * @group financial
 * @group financial-transaction
 */
class FinancialTransactionModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test if transactions are created successfully
     */
    public function test_create_transaction()
    {
        // TODO: Implement
        $this->markTestIncomplete();
    }

    /**
     * Test if transaction fees settle correctly
     */
    public function test_settle_default_transaction_fees()
    {
        Queue::fake();
        // Setup
        $accounts = $this->createInternalAccounts([10000, 0]);

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
        $this->assertCurrencyAmountIsEqual(-10000, $transactions['debit']->balance);
        $this->expectException(TransactionAlreadySettled::class);
        $transactions['debit']->settleBalance();

        $transactions['credit']->settleBalance();
        $transactions['credit']->save();
        $this->assertIsSettled($transactions['credit']);
        $this->assertCurrencyAmountIsEqual(10000, $transactions['credit']->balance);
        $this->expectException(TransactionAlreadySettled::class);
        $transactions['debit']->settleBalance();

        // Moving to creators Account
        $userAccount = $inAccount->owner->getInternalAccount($this->defaultSystem, $this->defaultCurrency);

        $transactions = $userAccount->moveTo($creatorAccount, 1000);

        // Prior Balances
        $this->assertCurrencyAmountIsEqual(10000, $transactions['debit']->calculateBalance());
        $this->assertCurrencyAmountIsEqual(0, $transactions['credit']->calculateBalance());

        $transactions['debit']->settleBalance();
        $transactions['debit']->save();
        $this->assertIsSettled($transactions['debit']);
        $this->assertCurrencyAmountIsEqual(9000, $transactions['debit']->balance);
        $this->expectException(TransactionAlreadySettled::class);
        $transactions['debit']->settleBalance();

        $transactions['credit']->settleBalance();
        $transactions['credit']->save();
        $this->assertIsSettled($transactions['credit']);
        $this->assertCurrencyAmountIsEqual(1000, $transactions['credit']->balance);
        $this->expectException(TransactionAlreadySettled::class);
        $transactions['debit']->settleBalance();

        // 2nd Transaction
        $transactions = $userAccount->moveTo($creatorAccount, 1000);

        // Prior Balances
        $this->assertCurrencyAmountIsEqual(9000, $transactions['debit']->calculateBalance());
        $this->assertCurrencyAmountIsEqual(1000, $transactions['credit']->calculateBalance());

        $transactions['debit']->settleBalance();
        $transactions['debit']->save();
        $this->assertIsSettled($transactions['debit']);
        $this->assertCurrencyAmountIsEqual(8000, $transactions['debit']->balance);
        $this->expectException(TransactionAlreadySettled::class);
        $transactions['debit']->settleBalance();

        $transactions['credit']->settleBalance();
        $transactions['credit']->save();
        $this->assertIsSettled($transactions['credit']);
        $this->assertCurrencyAmountIsEqual(2000, $transactions['credit']->balance);
        $this->expectException(TransactionAlreadySettled::class);
        $transactions['debit']->settleBalance();
    }

    /**
     *
     */
    public function test_fail_transaction_gracefully()
    {
        // TODO: Implement
        $this->markTestIncomplete();
    }


    /**
     * Setup Helper | Create Internal Accounts
     */
    private function createInternalAccounts($balances = [])
    {
        $accounts = [];
        foreach($balances as $balance) {
            array_push($accounts, Account::factory()->asInternal()->withBalance($balance)->create());
        }
        return $accounts;
    }
}
