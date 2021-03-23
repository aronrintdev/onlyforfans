<?php

namespace Tests\Unit\Financial;

use App\Models\Financial\Account;
use Illuminate\Support\Facades\DB;
use App\Models\Financial\Transaction;
use Illuminate\Support\Facades\Event;
use App\Enums\Financial\TransactionTypeEnum;
use App\Jobs\Financial\UpdateAccountBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Financial\Exceptions\Account\IncorrectTypeException;
use App\Models\Financial\Exceptions\TransactionAccountMismatchException;

/**
 * Unit Tests related to chargeback handling
 *
 * @group unit
 * @group financial
 * @group financial-chargeback
 *
 * @package Tests\Unit\Financial
 */
class ChargebackTest extends TestCase
{
    use RefreshDatabase;


    public function test_can_only_be_made_on_in_accounts()
    {
        Event::fake([UpdateAccountBalance::class]);
        $inAccount = Account::factory()->asIn()->create();
        $internalAccount = $inAccount->owner->getInternalAccount($this->defaultSystem, $this->defaultCurrency);
        $transactions = $inAccount->moveToInternal(1000);

        $this->expectException(IncorrectTypeException::class);
        $internalAccount->handleChargeback($transactions['credit']);
    }

    public function test_account_transaction_mismatch_exception_works()
    {
        Event::fake([UpdateAccountBalance::class]);
        $inAccount = Account::factory()->asIn()->create();
        $internalAccount = $inAccount->owner->getInternalAccount($this->defaultSystem, $this->defaultCurrency);
        $transactions = $inAccount->moveToInternal(1000);
        $this->expectException(TransactionAccountMismatchException::class);
        $inAccount->handleChargeback($transactions['credit']);
    }

    public function test_single_transaction_without_fees()
    {
        Event::fake([ UpdateAccountBalance::class ]);

        $inAccount = Account::factory()->asIn()->create();
        $internalAccount = $inAccount->owner->getInternalAccount($this->defaultSystem, $this->defaultCurrency);
        $transactions = $inAccount->moveToInternal(1000);
        $inAccount->settleBalance();
        $inAccount->save();
        $internalAccount->settleBalance();
        $internalAccount->save();
        $chargebackTransaction = $transactions['debit']; // The transaction being charged back
        $chargebackTransaction->refresh();

        $creatorAccount = Account::factory()->asInternal()->create();
        $paymentTransactions = $internalAccount->moveTo($creatorAccount, 1000);

        // Do Chargeback before creators account ballance and fees are settled.
        $chargebackTransactions = $inAccount->handleChargeback($chargebackTransaction);

        // Chargeback from internal account to in account
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $inAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'credit_amount' => 1000,
        ]);

        // Chargeback from user internal account to in account
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $internalAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 1000,
        ]);

        // Chargeback from creator to user internal account
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $internalAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'credit_amount' => 1000,
        ]);

        // Chargeback from creator Account
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $creatorAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 1000,
        ]);

        // Settle all balances
        $inAccount->settleBalance();
        $inAccount->save();
        $internalAccount->settleBalance();
        $internalAccount->save();
        $creatorAccount->settleBalance();
        $creatorAccount->save();

        $this->assertCurrencyAmountIsEqual(0, $inAccount->balance, 'In account balance back at zero');
        $this->assertCurrencyAmountIsEqual(0, $internalAccount->balance, 'Internal account balance back at zero');
        $this->assertCurrencyAmountIsEqual(0, $creatorAccount->balance, 'Creator account balance back at zero');
    }

    public function test_single_transaction_with_fees()
    {
        $this->markTestIncomplete();
    }

    public function test_multiple_transactions_without_fees()
    {
        $this->markTestIncomplete();
    }

    public function test_multiple_transactions_with_fees()
    {
        $this->markTestIncomplete();
    }

    public function test_multiple_transactions_with_mixed_fees()
    {
        $this->markTestIncomplete();
    }

    public function test_multiple_transaction_with_partial()
    {
        $this->markTestIncomplete();
    }

    public function test_creators_account_balance_can_go_negative()
    {
        $this->markTestIncomplete();
    }

    /**
     * If funds were only move to the owners internal account and no additional purchases were made.
     * @return void
     */
    public function test_no_posts_bought()
    {
        $this->markTestIncomplete();
    }

    /**
     * If the owner bought some posts, but part of the balance comes from the wallet
     * @return void
     */
    public function test_has_partial_wallet_balance()
    {
        $this->markTestIncomplete();
    }

    /**
     * If the owner still as money in wallet after chargeback is triggered
     * @return void
     */
    public function test_partial_wallet_balance_remaining()
    {
        $this->markTestIncomplete();
    }

}
