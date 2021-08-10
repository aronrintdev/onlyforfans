<?php

namespace Tests\Unit\Financial;

use App\Models\Financial\Account;
use Illuminate\Support\Facades\Bus;
use App\Jobs\CreateTransactionSummary;
use Tests\traits\Financial\NoHoldPeriod;
use Tests\Helpers\Financial\AccountHelpers;
use App\Models\Financial\TransactionSummary;
use App\Jobs\StartTransactionSummaryCreation;
use App\Enums\Financial\TransactionSummaryTypeEnum;
use App\Enums\Financial\TransactionTypeEnum;

/**
 * Unit test cases related to `App\Models\Financial\TransactionSummary` model
 *
 * @group unit
 * @group financial
 * @group financial-transaction-summary
 */
class TransactionSummaryModelTest extends TestCase
{
    use NoHoldPeriod;

    public $inAccount;
    public $fanAccount;
    public $creatorAccount;

    public function setUp(): void
    {
        parent::setUp();
        $accounts = AccountHelpers::createInternalAccounts([0, 0]);
        $this->fanAccount = $accounts[0];
        $this->creatorAccount = $accounts[1];

        $this->inAccount = Account::factory()->asIn()->create();

        $this->inAccount->moveToWallet(10000);
        $this->fanAccount = $this->inAccount->getWalletAccount();

        $this->fanAccount->settleBalance();
        $this->fanAccount->save();

        $this->fanAccount->moveTo($this->creatorAccount, 1000, [ 'type' => TransactionTypeEnum::SALE ]);
        $this->fanAccount->moveTo($this->creatorAccount, 2000, [ 'type' => TransactionTypeEnum::SALE ]);
        $this->fanAccount->moveTo($this->creatorAccount, 3000, [ 'type' => TransactionTypeEnum::SALE ]);
        $this->fanAccount->moveTo($this->creatorAccount, 2000, [ 'type' => TransactionTypeEnum::SALE ]);

        $this->creatorAccount->settleBalance();
        $this->creatorAccount->save();
    }

    /**
     * Check if new Bundle transaction summary is created correctly
     */
    public function test_bundle_transaction_summary()
    {
        $this->travel(5)->minutes();

        CreateTransactionSummary::dispatch($this->inAccount, TransactionSummaryTypeEnum::BUNDLE);
        $this->assertDatabaseHas(TransactionSummary::getTableName(), [
            'account_id' => $this->inAccount->getKey(),
            'type' => TransactionSummaryTypeEnum::BUNDLE,
            'balance' => -10000,
            'balance_delta' => -10000,
            'transactions_count' => 1,
            'credit_count' => 0,
            'debit_count' => 1,
            'credit_sum' => 0,
            'debit_sum' => 10000,
            'credit_average' => 0,
            'debit_average' => 10000,
        ], $this->getConnectionString());

        CreateTransactionSummary::dispatch($this->fanAccount, TransactionSummaryTypeEnum::BUNDLE);
        $this->assertDatabaseHas(TransactionSummary::getTableName(), [
            'account_id' => $this->fanAccount->getKey(),
            'type' => TransactionSummaryTypeEnum::BUNDLE,
            'balance' => 2000,
            'balance_delta' => 2000,
            'transactions_count' => 5,
            'credit_count' => 1,
            'debit_count' => 4,
            'credit_sum' => 10000,
            'debit_sum' => 8000,
            'credit_average' => 10000,
            'debit_average' => 2000,
        ], $this->getConnectionString());

        CreateTransactionSummary::dispatch($this->creatorAccount, TransactionSummaryTypeEnum::BUNDLE);
        $this->assertDatabaseHas(TransactionSummary::getTableName(), [
            'account_id' => $this->creatorAccount->getKey(),
            'type' => TransactionSummaryTypeEnum::BUNDLE,
            'balance' => 8000,
            'balance_delta' => 8000,
            'transactions_count' => 4,
            'credit_count' => 4,
            'debit_count' => 0,
            'credit_sum' => 8000,
            'debit_sum' => 0,
            'credit_average' => 2000,
            'debit_average' => 0,
        ], $this->getConnectionString());

        $this->travelBack();
    }

    /**
     * Check if daily TransactionSummaries are created correctly.
     */
    public function test__daily_transaction_summaries()
    {
        $this->travel(1)->days();
        Bus::batch([
            new StartTransactionSummaryCreation(TransactionSummaryTypeEnum::DAILY),
        ])->dispatch();


        $this->assertEquals(3, TransactionSummary::where('type', TransactionSummaryTypeEnum::DAILY)->count());
        $this->travelBack();
    }

    /**
     * Check if weekly TransactionSummaries are created correctly.
     */
    public function test_weekly_transaction_summaries()
    {
        $this->travel(1)->weeks();
        Bus::batch([
            new StartTransactionSummaryCreation(TransactionSummaryTypeEnum::WEEKLY),
        ])->dispatch();

        $this->assertEquals(3, TransactionSummary::where('type', TransactionSummaryTypeEnum::WEEKLY)->count());
        $this->travelBack();
    }

    public function test_monthly_transaction_summaries()
    {
        $this->travel(1)->months();
        Bus::batch([
            new StartTransactionSummaryCreation(TransactionSummaryTypeEnum::MONTHLY),
        ])->dispatch();

        $this->assertEquals(3, TransactionSummary::where('type', TransactionSummaryTypeEnum::MONTHLY)->count());
        $this->travelBack();
    }

    public function test_yearly_transaction_summaries()
    {
        $this->travel(1)->years();
        Bus::batch([
            new StartTransactionSummaryCreation(TransactionSummaryTypeEnum::YEARLY),
        ])->dispatch();

        $this->assertEquals(3, TransactionSummary::where('type', TransactionSummaryTypeEnum::YEARLY)->count());
        $this->travelBack();
    }


}