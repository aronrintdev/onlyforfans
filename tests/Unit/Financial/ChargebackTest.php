<?php

namespace Tests\Unit\Financial;

use App\Models\Financial\Account;
use App\Models\Financial\Transaction;
use Illuminate\Support\Facades\Event;
use App\Enums\Financial\TransactionTypeEnum;
use App\Events\FinancialFlagRaised;
use App\Jobs\Financial\UpdateAccountBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Financial\Exceptions\Account\IncorrectTypeException;
use App\Models\Financial\Exceptions\TransactionAccountMismatchException;
use Tests\Helpers\Financial\AccountHelpers;
use Tests\traits\Financial\NoHoldPeriod;

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
    use RefreshDatabase,
        NoHoldPeriod;


    #region Error Handling

    /**
     * Properly throws exceptions
     * @return void
     */
    public function test_can_only_be_made_on_in_accounts()
    {
        Event::fake([UpdateAccountBalance::class]);
        $inAccount = Account::factory()->asIn()->create();
        $internalAccount = $inAccount->owner->getInternalAccount($this->defaultSystem, $this->defaultCurrency);
        $transactions = $inAccount->moveToInternal(1000);

        $this->expectException(IncorrectTypeException::class);
        $internalAccount->handleChargeback($transactions['credit']);
    }

    /**
     * Properly throws exception
     * @return void
     */
    public function test_account_transaction_mismatch_exception_works()
    {
        Event::fake([UpdateAccountBalance::class]);
        $inAccount = Account::factory()->asIn()->create();
        $internalAccount = $inAccount->owner->getInternalAccount($this->defaultSystem, $this->defaultCurrency);
        $transactions = $inAccount->moveToInternal(1000);
        $this->expectException(TransactionAccountMismatchException::class);
        $inAccount->handleChargeback($transactions['credit']);
    }

    #endregion

    #region Single Transaction

    /**
     * Single transaction chargeback before fees are settled on transaction
     * @return void
     */
    public function test_single_transaction_without_fees()
    {
        Event::fake([ UpdateAccountBalance::class ]);

        [$inAccount, $internalAccount, $chargebackTransaction] = $this->setupUserAccounts(1000);

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
        AccountHelpers::settleAccounts([ $inAccount, $internalAccount, $creatorAccount, ]);

        $this->assertHasBalanceOf(0, $inAccount, 'In account balance back at zero');
        $this->assertHasBalanceOf(0, $inAccount, 'In account balance back at zero');
        $this->assertHasBalanceOf(0, $internalAccount, 'Internal account balance back at zero');
        $this->assertHasBalanceOf(0, $creatorAccount, 'Creator account balance back at zero');
    }

    /**
     * Single transaction chargeback with settled fees
     * @return void
     */
    public function test_single_transaction_with_fees()
    {
        Event::fake([UpdateAccountBalance::class]);

        [$inAccount, $internalAccount, $chargebackTransaction] = $this->setupUserAccounts(1000);

        $creatorAccount = Account::factory()->asInternal()->create();
        // Payment to creator
        $paymentTransactions = $internalAccount->moveTo($creatorAccount, 1000);

        // Settle all balances
        AccountHelpers::settleAccounts([
            $inAccount,
            $internalAccount,
            $creatorAccount, // This will generate fees
        ]);

        $platformFeesAccount = Account::getFeeAccount('platformFee', $this->defaultSystem, $this->defaultCurrency);
        $taxAccount = Account::getFeeAccount('tax', $this->defaultSystem, $this->defaultCurrency);

        AccountHelpers::settleAccounts([
            $creatorAccount,
            $platformFeesAccount,
            $taxAccount,
        ]);

        // Check fees accounts
        // 30% => 300, 5% => 50
        $this->assertHasBalanceOf(300, $platformFeesAccount, 'Platform Fee was charged correctly');
        $this->assertHasBalanceOf(50, $taxAccount, 'Tax was charged correctly');

        // Perform chargeback
        $chargebackTransactions = $inAccount->handleChargeback($chargebackTransaction);

        // Platform Fee Chargeback
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $platformFeesAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 300,
        ]);

        // Tax Chargeback
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $taxAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 50,
        ]);

        // Creator Chargeback
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $creatorAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 1000,
        ]);

        // User Chargeback
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $internalAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 1000,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $inAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'credit_amount' => 1000,
        ]);

        // Settle balances
        AccountHelpers::settleAccounts([
            $inAccount,
            $internalAccount,
            $creatorAccount,
            $platformFeesAccount,
            $taxAccount,
        ]);

        // All balances at 0
        $this->assertHasBalanceOf(0, $inAccount, 'In account balance back at zero');
        $this->assertHasBalanceOf(0, $internalAccount, 'Internal account balance back at zero');
        $this->assertHasBalanceOf(0, $creatorAccount, 'Creator account balance back at zero');
        $this->assertHasBalanceOf(0, $platformFeesAccount, 'Platform fees account balance back at zero');
        $this->assertHasBalanceOf(0, $taxAccount, 'Tax account balance back at zero');
    }

    #endregion

    #region Multiple Transactions

    /**
     * Simulate batched purchases before creator accounts have time to settle balance and calculate fees
     * @return void
     */
    public function test_multiple_transactions_without_fees()
    {
        Event::fake([UpdateAccountBalance::class]);

        // User's accounts
        [$inAccount, $internalAccount, $chargebackTransaction] = $this->setupUserAccounts(1000);

        // Payments to multiple creators
        $creatorAccount1 = Account::factory()->asInternal()->create();
        $creatorAccount2 = Account::factory()->asInternal()->create();
        $creatorAccount3 = Account::factory()->asInternal()->create();

        $internalAccount->moveTo($creatorAccount1, 500);
        $internalAccount->moveTo($creatorAccount2, 300);
        $internalAccount->moveTo($creatorAccount3, 200);

        // Chargeback
        $inAccount->handleChargeback($chargebackTransaction);

        // Chargeback Transactions
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $creatorAccount1->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 500,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $creatorAccount2->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 300,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $creatorAccount3->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 200,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $internalAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 1000,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $inAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'credit_amount' => 1000,
        ]);

        AccountHelpers::settleAccounts([
            $inAccount, $internalAccount, $creatorAccount1, $creatorAccount2, $creatorAccount3
        ]);

        $this->assertHasBalanceOf(0, $inAccount);
        $this->assertHasBalanceOf(0, $internalAccount);
        $this->assertHasBalanceOf(0, $creatorAccount1);
        $this->assertHasBalanceOf(0, $creatorAccount2);
        $this->assertHasBalanceOf(0, $creatorAccount3);
    }

    /**
     * Simulate batched purchases after creator accounts have time to settle balance and calculate fees
     * @return void
     */
    public function test_multiple_transactions_with_fees()
    {
        Event::fake([UpdateAccountBalance::class]);
        // User's accounts
        [$inAccount, $internalAccount, $chargebackTransaction] = $this->setupUserAccounts(1000);

        // Payments to multiple creators
        $creatorAccount1 = Account::factory()->asInternal()->create();
        $creatorAccount2 = Account::factory()->asInternal()->create();
        $creatorAccount3 = Account::factory()->asInternal()->create();

        $internalAccount->moveTo($creatorAccount1, 500);
        $internalAccount->moveTo($creatorAccount2, 300);
        $internalAccount->moveTo($creatorAccount3, 200);

        $platformFeesAccount = Account::getFeeAccount('platformFee', $this->defaultSystem, $this->defaultCurrency);
        $taxAccount = Account::getFeeAccount('tax', $this->defaultSystem, $this->defaultCurrency);

        // Settle balances and fees
        AccountHelpers::settleAccounts([
            $inAccount,
            $internalAccount,
            $creatorAccount1,
            $creatorAccount2,
            $creatorAccount3,
            $platformFeesAccount,
            $taxAccount
        ]);

        // Chargeback
        $inAccount->handleChargeback($chargebackTransaction);

        // Chargeback Transactions
        $this->assertEquals(3, Transaction::where([
            'account_id' => $platformFeesAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
        ])->count());
        $this->assertEquals(3, Transaction::where([
            'account_id' => $taxAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
        ])->count());

        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $creatorAccount1->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 500,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $creatorAccount2->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 300,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $creatorAccount3->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 200,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $internalAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 1000,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $inAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'credit_amount' => 1000,
        ]);

        AccountHelpers::settleAccounts([
            $inAccount,
            $internalAccount,
            $creatorAccount1,
            $creatorAccount2,
            $creatorAccount3,
            $platformFeesAccount,
            $taxAccount
        ]);

        $this->assertHasBalanceOf(0, $inAccount);
        $this->assertHasBalanceOf(0, $internalAccount);
        $this->assertHasBalanceOf(0, $creatorAccount1);
        $this->assertHasBalanceOf(0, $creatorAccount2);
        $this->assertHasBalanceOf(0, $creatorAccount3);
        $this->assertHasBalanceOf(0, $platformFeesAccount);
        $this->assertHasBalanceOf(0, $taxAccount);
    }

    /**
     * Simulate batched purchases where some creator accounts have had time to settle balance and calculate fees and
     * some have not
     * @return void
     */
    public function test_multiple_transactions_with_mixed_fees()
    {
        Event::fake([UpdateAccountBalance::class]);
        // User's accounts
        [$inAccount, $internalAccount, $chargebackTransaction] = $this->setupUserAccounts(1000);

        // Payments to multiple creators
        $creatorAccount1 = Account::factory()->asInternal()->create();
        $creatorAccount2 = Account::factory()->asInternal()->create();
        $creatorAccount3 = Account::factory()->asInternal()->create();

        $internalAccount->moveTo($creatorAccount1, 500);
        $internalAccount->moveTo($creatorAccount2, 300);
        $internalAccount->moveTo($creatorAccount3, 200);

        $platformFeesAccount = Account::getFeeAccount('platformFee', $this->defaultSystem, $this->defaultCurrency);
        $taxAccount = Account::getFeeAccount('tax', $this->defaultSystem, $this->defaultCurrency);

        // Settle some balances and fees
        AccountHelpers::settleAccounts([
            $inAccount,
            $internalAccount,
            $creatorAccount1,
            $creatorAccount2,
            $platformFeesAccount,
            $taxAccount
        ]);

        // Chargeback
        $inAccount->handleChargeback($chargebackTransaction);

        // Chargeback Transactions
        $this->assertEquals(2, Transaction::where([
            'account_id' => $platformFeesAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
        ])->count());
        $this->assertEquals(2, Transaction::where([
            'account_id' => $taxAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
        ])->count());

        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $creatorAccount1->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 500,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $creatorAccount2->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 300,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $creatorAccount3->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 200,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $internalAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 1000,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $inAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'credit_amount' => 1000,
        ]);

        AccountHelpers::settleAccounts([
            $inAccount,
            $internalAccount,
            $creatorAccount1,
            $creatorAccount2,
            $creatorAccount3,
            $platformFeesAccount,
            $taxAccount
        ]);

        $this->assertHasBalanceOf(0, $inAccount);
        $this->assertHasBalanceOf(0, $internalAccount);
        $this->assertHasBalanceOf(0, $creatorAccount1);
        $this->assertHasBalanceOf(0, $creatorAccount2);
        $this->assertHasBalanceOf(0, $creatorAccount3);
        $this->assertHasBalanceOf(0, $platformFeesAccount);
        $this->assertHasBalanceOf(0, $taxAccount);
    }

    /**
     * Batched purchases where last chargeback is partial charge back
     * @return void
     */
    public function test_multiple_transaction_with_partial()
    {
        Event::fake([UpdateAccountBalance::class]);
        // User's accounts
        $inAccount = Account::factory()->asIn()->create();
        $internalAccount = $inAccount->owner->getInternalAccount($this->defaultSystem, $this->defaultCurrency);
        $transactions = $inAccount->moveToInternal(900);
        $inAccount->moveToInternal(100);
        AccountHelpers::settleAccounts([$inAccount, $internalAccount]);
        $chargebackTransaction = $transactions['debit'];

        // Payments to multiple creators
        $creatorAccount1 = Account::factory()->asInternal()->create();
        $creatorAccount2 = Account::factory()->asInternal()->create();
        $creatorAccount3 = Account::factory()->asInternal()->create();

        $internalAccount->moveTo($creatorAccount1, 500);
        $internalAccount->moveTo($creatorAccount2, 300);
        $internalAccount->moveTo($creatorAccount3, 200);

        $platformFeesAccount = Account::getFeeAccount('platformFee', $this->defaultSystem, $this->defaultCurrency);
        $taxAccount = Account::getFeeAccount('tax', $this->defaultSystem, $this->defaultCurrency);

        // Settle balances and fees
        AccountHelpers::settleAccounts([
            $inAccount,
            $internalAccount,
            $creatorAccount1,
            $creatorAccount2,
            $creatorAccount3,
            $platformFeesAccount,
            $taxAccount
        ]);

        // Chargeback
        $inAccount->handleChargeback($chargebackTransaction);

        // Chargeback Transactions
        $this->assertEquals(3, Transaction::where([
            'account_id' => $platformFeesAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
        ])->count());
        $this->assertEquals(3, Transaction::where([
            'account_id' => $taxAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
        ])->count());

        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $creatorAccount1->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 500,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $creatorAccount2->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 300,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $creatorAccount3->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 200,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $creatorAccount3->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK_PARTIAL,
            'credit_amount' => 100,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $internalAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'debit_amount' => 900,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $inAccount->getKey(),
            'type' => TransactionTypeEnum::CHARGEBACK,
            'credit_amount' => 900,
        ]);

        AccountHelpers::settleAccounts([
            $inAccount,
            $internalAccount,
            $creatorAccount1,
            $creatorAccount2,
            $creatorAccount3,
            $platformFeesAccount,
            $taxAccount
        ]);

        $this->assertHasBalanceOf(-100, $inAccount);
        $this->assertHasBalanceOf(0, $internalAccount);
        $this->assertHasBalanceOf(0, $creatorAccount1);
        $this->assertHasBalanceOf(0, $creatorAccount2);
        // 65% of 100
        $this->assertHasBalanceOf(65, $creatorAccount3);
        // 30% of 100
        $this->assertHasBalanceOf(30, $platformFeesAccount);
        // 5% of 100
        $this->assertHasBalanceOf(5, $taxAccount);
    }

    #endregion

    /**
     * Case where creator has taken funds out of internal account should make creators account balance go negative.
     * @return void
     */
    public function test_creators_account_balance_can_go_negative()
    {
        Event::fake([UpdateAccountBalance::class]);
        [$inAccount, $internalAccount, $chargebackTransaction] = $this->setupUserAccounts(1000);

        // Creators account
        $creatorAccount = Account::factory()->asInternal()->create();
        $creatorOutAccount = Account::factory()->asOut()->sameOwnerAs($creatorAccount)->create();

        $internalAccount->moveTo($creatorAccount, 1000);

        $platformFeesAccount = Account::getFeeAccount('platformFee', $this->defaultSystem, $this->defaultCurrency);
        $taxAccount = Account::getFeeAccount('tax', $this->defaultSystem, $this->defaultCurrency);

        // Settle balances and fees
        AccountHelpers::settleAccounts([
            $inAccount,
            $internalAccount,
            $creatorAccount,
            $platformFeesAccount,
            $taxAccount
        ]);

        // Move to Out account
        $creatorAccount->moveTo($creatorOutAccount, 650);

        AccountHelpers::settleAccounts([
            $creatorAccount,
            $creatorOutAccount
        ]);

        // Perform Chargeback
        $inAccount->handleChargeBack($chargebackTransaction);

        AccountHelpers::settleAccounts([
            $inAccount,
            $internalAccount,
            $creatorAccount,
            $platformFeesAccount,
            $taxAccount
        ]);

        $this->assertHasBalanceOf(-650, $creatorAccount);
        // Fees get paid back into chargeback
        $this->assertHasBalanceOf(0, $platformFeesAccount);
        $this->assertHasBalanceOf(0, $taxAccount);
    }

    #region Has Wallet Balance Tests

    /**
     * If funds were only move to the owners internal account and no additional purchases were made.
     * @return void
     */
    public function test_no_posts_bought()
    {
        Event::fake([UpdateAccountBalance::class]);

        [$inAccount, $internalAccount, $chargebackTransaction] = $this->setupUserAccounts(1000);

        // Chargeback
        $inAccount->handleChargeback($chargebackTransaction);

        AccountHelpers::settleAccounts([$inAccount, $internalAccount]);

        $this->assertHasBalanceOf(0, $inAccount, 'In account balance back at zero');
        $this->assertHasBalanceOf(0, $internalAccount, 'Internal account balance back at zero');
    }

    /**
     * If the owner bought some posts, but part of the balance comes from the wallet
     * @return void
     */
    public function test_has_partial_wallet_balance()
    {
        Event::fake([UpdateAccountBalance::class]);

        [$inAccount, $internalAccount, $chargebackTransaction] = $this->setupUserAccounts(1000);

        $creatorAccount = Account::factory()->asInternal()->create();
        $internalAccount->moveTo($creatorAccount, 1000);

        $inAccount->moveToInternal(300);
        AccountHelpers::settleAccounts([$inAccount, $internalAccount, $creatorAccount]);

        $inAccount->handleChargeback($chargebackTransaction);

        AccountHelpers::settleAccounts([$inAccount, $internalAccount, $creatorAccount]);

        $this->assertHasBalanceOf(-300, $inAccount, 'In account at -300 balance after chargeback');
        $this->assertHasBalanceOf(0, $internalAccount, 'Internal account at 0 balance after chargeback');
        $this->assertHasBalanceOf(195, $creatorAccount, 'Creator account 195 after partial chargeback');
    }

    /**
     * If the owner bought posts, but all of chargeback comes from wallet balance
     * @return void
     */
    public function test_has_full_wallet_balance()
    {
        Event::fake([UpdateAccountBalance::class]);

        [$inAccount, $internalAccount, $chargebackTransaction] = $this->setupUserAccounts(1000);

        $creatorAccount = Account::factory()->asInternal()->create();
        $internalAccount->moveTo($creatorAccount, 1000);

        $inAccount->moveToInternal(1000);
        AccountHelpers::settleAccounts([$inAccount, $internalAccount, $creatorAccount]);

        $inAccount->handleChargeback($chargebackTransaction);
        AccountHelpers::settleAccounts([$inAccount, $internalAccount, $creatorAccount]);

        $this->assertHasBalanceOf(-1000, $inAccount, 'In account at -300 balance after chargeback');
        $this->assertHasBalanceOf(0, $internalAccount, 'Internal account at 0 balance after chargeback');
        $this->assertHasBalanceOf(650, $creatorAccount, 'Creator account at 650');
    }

    /**
     * If the owner still as money in wallet after chargeback is triggered
     * @return void
     */
    public function test_partial_wallet_balance_remaining()
    {
        Event::fake([UpdateAccountBalance::class]);

        [$inAccount, $internalAccount, $chargebackTransaction] = $this->setupUserAccounts(1000);

        $creatorAccount = Account::factory()->asInternal()->create();
        $internalAccount->moveTo($creatorAccount, 1000);

        $inAccount->moveToInternal(1200);
        AccountHelpers::settleAccounts([$inAccount, $internalAccount, $creatorAccount]);

        $inAccount->handleChargeback($chargebackTransaction);
        AccountHelpers::settleAccounts([$inAccount, $internalAccount, $creatorAccount]);

        $this->assertHasBalanceOf(-1200, $inAccount, 'In account at -300 balance after chargeback');
        $this->assertHasBalanceOf(200, $internalAccount, 'Internal account at 0 balance after chargeback');
        $this->assertHasBalanceOf(650, $creatorAccount, 'Creator account at 650');
    }

    #endregion

    #region Account Blocking and Flag Rasing

    public function test_account_blocked_from_making_transactions()
    {
        Event::fake([UpdateAccountBalance::class]);

        [$inAccount, $internalAccount, $chargebackTransaction] = $this->setupUserAccounts(1000);
        $creatorAccount = Account::factory()->asInternal()->create();
        $internalAccount->moveTo($creatorAccount, 1000);

        $inAccount->handleChargeback($chargebackTransaction);
        AccountHelpers::settleAccounts([$inAccount, $internalAccount, $creatorAccount]);

        $this->assertCanNotMakeTransactions($inAccount);
    }

    public function test_admin_flag_is_raised()
    {
        Event::fake([UpdateAccountBalance::class, FinancialFlagRaised::class]);

        [$inAccount, $internalAccount, $chargebackTransaction] = $this->setupUserAccounts(1000);
        $creatorAccount = Account::factory()->asInternal()->create();
        $internalAccount->moveTo($creatorAccount, 1000);

        $inAccount->handleChargeback($chargebackTransaction);
        AccountHelpers::settleAccounts([$inAccount, $internalAccount, $creatorAccount]);

        $this->assertDatabaseHas($this->tableNames['flag'], [
            'model_type' => $inAccount->getMorphString(),
            'model_id' => $inAccount->getKey(),
            'column' => 'can_make_transactions',
            'delta_before' => true,
            'delta_after' => false,
        ]);

        Event::assertDispatched(FinancialFlagRaised::class);
    }

    #endregion

    #region Helpers
    /**
     * Common Setup for many tests
     * @param int $amount
     * @return array `[ $inAccount, $internalAccount, $chargebackTransaction ]`
     */
    private function setupUserAccounts(int $amount)
    {
        $items = AccountHelpers::loadWallet($amount);
        return [ $items['in'], $items['internal'], $items['transactions']['debit'] ];
    }


    #endregion

}
