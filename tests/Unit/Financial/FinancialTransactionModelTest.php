<?php

namespace Tests\Unit\Financial;

use Tests\TestCase;
use App\Models\Financial\Account;
use App\Models\Financial\SystemOwner;
use App\Models\Financial\Transaction;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

/**
 * Unit Tests for the `App\Models\Financial\Transaction` Model
 */
class FinancialTransactionModelTest extends TestCase
{
    use RefreshDatabase;

    protected $defaultSystem;
    protected $defaultCurrency;
    protected $accountTableName;
    protected $transactionTableName;
    protected $systemOwnerTableName;

    public function setUp(): void
    {
        parent::setUp();
        $this->defaultSystem = Config::get('transactions.default');
        $this->defaultCurrency = Config::get('transactions.defaultCurrency');
        $this->accountTableName = app(Account::class)->getTable();
        $this->transactionTableName = app(Transaction::class)->getTable();
        $this->systemOwnerTableName = app(SystemOwner::class)->getTable();
    }



    /**
     * @group unit
     * @group financial
     */
    public function test_create_transaction()
    {
        // TODO: Implement
        $this->markTestIncomplete();
    }

    /**
     * @group unit
     * @group financial
     */
    public function test_settle_transaction()
    {
        // TODO: Implement
        $this->markTestIncomplete();
    }

    /**
     * @group unit
     * @group financial
     */
    public function test_fail_transaction()
    {
        // TODO: Implement
        $this->markTestIncomplete();
    }

    /**
     * @group unit
     * @group financial
     */
    public function test_update_transaction_balance()
    {
        // TODO: Implement
        $this->markTestIncomplete();
    }

    /**
     * @group unit
     * @group financial
     */
    public function test_settle_default_transaction_fees()
    {
        Queue::fake();
        // Setup
        $fromAccount = Account::factory()->asTypeInternal()->withBalance(10000)->create();
        $toAccount = Account::factory()->asTypeInternal()->create();

        // Default Fees:
        // PlatformFee => 30% | 300
        // Tax => 5% | 50
        $fromAccount->moveTo($toAccount, 1000);
        $transaction = Transaction::where('account_id', $toAccount->getKey())->first();

        $transaction->settleFees();

        $platformFeeAccount = Account::getFeeAccount('platformFee', $this->defaultSystem, $this->defaultCurrency);
        $taxAccount = Account::getFeeAccount('tax', $this->defaultSystem, $this->defaultCurrency);

        // PlatformFee Transaction Set | 1000 * 0.3 = 300
        $this->assertDatabaseHas($this->transactionTableName, [
            'account_id' => $toAccount->getKey(),
            'debit_amount' => 300,
        ]);
        $this->assertDatabaseHas($this->transactionTableName, [
            'account_id' => $platformFeeAccount->getKey(),
            'credit_amount' => 300,
        ]);

        // Tax Transaction Set | 1000 * 0.05 = 50
        $this->assertDatabaseHas($this->transactionTableName, [
            'account_id' => $toAccount->getKey(),
            'debit_amount' => 50,
        ]);
        $this->assertDatabaseHas($this->transactionTableName, [
            'account_id' => $taxAccount->getKey(),
            'credit_amount' => 50,
        ]);
    }

    /**
     * @group unit
     * @group financial
     */
    public function test_balance_verification()
    {
        // TODO: Implement
        $this->markTestIncomplete();
    }
}
