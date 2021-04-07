<?php

namespace Tests\Unit\Financial;

use App\Models\User;
use App\Models\Financial\Account;
use App\Models\Financial\SystemOwner;
use App\Models\Financial\Transaction;
use Illuminate\Support\Facades\Event;
use App\Enums\Financial\AccountTypeEnum;
use Tests\Helpers\Financial\AccountHelpers;

use App\Jobs\Financial\UpdateAccountBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Financial\Exceptions\InvalidTransactionAmountException;
use App\Models\Financial\Exceptions\Account\InsufficientFundsException;
use App\Models\Financial\Exceptions\Account\TransactionNotAllowedException;
use Tests\traits\Financial\NoHoldPeriod;

/**
 * Unit Tests for the `App\Models\Financial\Account` Model
 *
 * @group unit
 * @group financial
 * @group financial-account
 *
 * @package Tests\Unit\Financial
 */
class AccountModelTest extends TestCase
{
    use RefreshDatabase,
        NoHoldPeriod;

    /**
     * In account is created correctly
     */
    public function test_create_in_account()
    {
        // TODO: Implement
        $this->markTestIncomplete();
    }

    /**
     * Internal account is created correctly
     */
    public function test_create_user_internal_account()
    {
        $user = User::factory()->create();

        $account = $user->createInternalAccount($this->defaultSystem, $this->defaultCurrency);

        $this->assertInstanceOf(Account::class, $account);
        $this->assertDatabaseHas($this->tableNames['account'], [
            'id' => $account->id,
            'owner_id' => $user->id,
            'type' => AccountTypeEnum::INTERNAL
        ]);
    }

    /**
     * User's Internal account is created and retrieved when using getInternalAccount()
     * @depends test_create_user_internal_account
     */
    public function test_get_user_internal_account()
    {
        $user = User::factory()->create();

        // Test when user doesnt have internal account
        $account = $user->getInternalAccount($this->defaultSystem, $this->defaultCurrency);

        $this->assertInstanceOf(Account::class, $account);
        $this->assertDatabaseHas($this->tableNames['account'], [
            'id' => $account->id,
            'owner_id' => $user->id,
            'type' => AccountTypeEnum::INTERNAL
        ]);

        $accountId = $account->id;

        unset($account);

        $account = $user->getInternalAccount($this->defaultSystem, $this->defaultCurrency);
        $this->assertInstanceOf(Account::class, $account);
        $this->assertEquals($accountId, $account->id);
    }

    /**
     * Out account is created correctly
     */
    public function test_create_out_account()
    {
        // TODO: Implement
        $this->markTestIncomplete();
    }

    public function test_move_negative_amount_fails()
    {
        $accounts = AccountHelpers::createInternalAccounts([1000, 1000]);
        $this->expectException(InvalidTransactionAmountException::class);
        $accounts[0]->moveTo($accounts[1], -1);
    }

    public function test_move_zero_amount_fails()
    {
        $accounts = AccountHelpers::createInternalAccounts([1000, 1000]);
        $this->expectException(InvalidTransactionAmountException::class);
        $accounts[0]->moveTo($accounts[1], 0);
    }

    /**
     * Move funds from in account -> internal account
     * @depends test_get_user_internal_account
     */
    public function test_move_to_internal_account_from_in_account()
    {
        $inAccount = Account::factory()->asIn()->create();
        $user = $inAccount->owner;

        $inAccount->moveToInternal(100);

        $this->assertHasBalanceOf(-100, $inAccount);

        // Created missing Internal account
        $this->assertDatabaseHas($this->tableNames['account'], [
            'owner_id' => $user->id,
            'type' => AccountTypeEnum::INTERNAL
        ]);

        $internalAccount = $user->getInternalAccount($this->defaultSystem, $this->defaultCurrency);

        // Has from transaction
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $inAccount->getKey(),
            'credit_amount' => 0,
            'debit_amount' => 100,
            'currency' => $this->defaultCurrency,
        ]);

        // Has to transaction
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $internalAccount->getKey(),
            'credit_amount' => 100,
            'debit_amount' => 0,
            'currency' => $this->defaultCurrency,
        ]);

        $fromTransaction = Transaction::where('account_id', $inAccount->getKey())->latest()->first();
        $toTransaction = Transaction::where('account_id', $internalAccount->getKey())->latest()->first();

        // Correct reference ids
        $this->assertEquals($fromTransaction->id, $toTransaction->reference_id);
        $this->assertEquals($toTransaction->id, $fromTransaction->reference_id);
    }

    /**
     * Internal accounts need to have enough balance
     * @depends test_move_to_internal_account_from_internal_account
     */
    public function test_move_with_insufficient_funds_fails()
    {
        $account = Account::factory()->asInternal()->withBalance(1000)->create();
        $toAccount = Account::factory()->asInternal()->withBalance(0)->create();

        $this->expectException(InsufficientFundsException::class);
        $account->moveTo($toAccount, 20000);
    }

    /**
     * Move funds from internal account -> internal account
     */
    public function test_move_to_internal_account_from_internal_account()
    {
        Event::fake([ UpdateAccountBalance::class ]);

        $account = Account::factory()->asInternal()->withBalance(1000)->create();
        $toAccount = Account::factory()->asInternal()->withBalance(0)->create();

        $account->moveTo($toAccount, 300);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $account->getKey(),
            'credit_amount' => 0,
            'debit_amount' => 300,
            'currency' => $this->defaultCurrency,
        ]);
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $toAccount->getKey(),
            'credit_amount' => 300,
            'debit_amount' => 0,
            'currency' => $this->defaultCurrency,
        ]);

        $this->assertHasBalanceOf(700, $account);

        Event::assertDispatched(UpdateAccountBalance::class);
        // Queue::assertPushed(UpdateAccountBalance::class);
    }

    #region test_blocked_account_cannot_make_transactions
    /**
     *
     */
    public function test_blocked_account_cannot_make_transactions_variant_1()
    {
        $inAccount = Account::factory()->asIn()->transactionsBlocked()->create();

        $this->expectException(TransactionNotAllowedException::class);
        $inAccount->moveToInternal(100);
    }

    public function test_blocked_account_cannot_make_transactions_variant_2()
    {
        $blockedAccount1 = Account::factory()->asInternal()->withBalance(200)
            ->transactionsBlocked()->create();
        $blockedAccount2 = Account::factory()->asInternal()->withBalance(200)
            ->transactionsBlocked()->create();
        $this->expectException(TransactionNotAllowedException::class);
        $blockedAccount1->moveTo($blockedAccount2, 100);
    }

    public function test_blocked_account_cannot_make_transactions_variant_3()
    {
        $blockedAccount1 = Account::factory()->asInternal()->withBalance(200)
            ->transactionsBlocked()->create();
        $unblockedAccount = Account::factory()->asInternal()->withBalance(200)
            ->transactionsAllowed()->create();
        $this->expectException(TransactionNotAllowedException::class);
        $blockedAccount1->moveTo($unblockedAccount, 100);
    }

    public function test_blocked_account_cannot_make_transactions_variant_4()
    {
        $blockedAccount1 = Account::factory()->asInternal()->withBalance(200)
            ->transactionsBlocked()->create();
        $unblockedAccount = Account::factory()->asInternal()->withBalance(200)
            ->transactionsAllowed()->create();
        $this->expectException(TransactionNotAllowedException::class);
        $unblockedAccount->moveTo($blockedAccount1, 100);
    }
    #endregion


    /**
     * Account balance is settled correctly
     * @depends test_move_to_internal_account_from_in_account
     * @depends test_move_to_internal_account_from_internal_account
     * @return void
     */
    public function test_settle_account_balance()
    {
        Event::fake([ UpdateAccountBalance::class ]);
        $items = AccountHelpers::loadWallet(1000);
        $this->assertHasBalanceOf(-1000, $items['in']);
        $this->assertHasBalanceOf(1000, $items['internal']);
        $creator = Account::factory()->asInternal()->create();
        $items['internal']->moveTo($creator, 1000);
        AccountHelpers::settleAccounts([$items['in'], $items['internal'], $creator]);

        $this->assertHasBalanceOf(-1000, $items['in']);
        $this->assertHasBalanceOf(0, $items['internal']);
        $this->assertHasBalanceOf(650, $creator);
    }

    /**
     * Test that accounts verify correctly.
     */
    public function test_verify_account()
    {
        // TODO: Implement
        $this->markTestIncomplete();
    }

    /**
     * A fees account is retrieved correctly.
     */
    public function test_get_system_account()
    {
        $account = Account::getFeeAccount('platformFees', $this->defaultSystem, $this->defaultCurrency);
        $this->assertInstanceOf(Account::class, $account);

        // System Owner Created
        $this->assertDatabaseHas($this->tableNames['systemOwner'], [
            'name' => 'platformFees',
            'system' => $this->defaultSystem,
        ]);

        $systemOwner = SystemOwner::where('name', 'platformFees')->where('system', $this->defaultSystem)->first();

        // Account was created
        $this->assertDatabaseHas($this->tableNames['account'], [
            'owner_id' => $systemOwner->getKey(),
            'type' => AccountTypeEnum::INTERNAL,
        ]);
    }
}
