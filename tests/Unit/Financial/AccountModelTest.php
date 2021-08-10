<?php

namespace Tests\Unit\Financial;

use App\Models\User;
use App\Models\Financial\Account;
use App\Models\Financial\SystemOwner;
use App\Models\Financial\Transaction;
use Illuminate\Support\Facades\Event;
use App\Enums\Financial\AccountTypeEnum;
use App\Enums\Financial\TransactionTypeEnum;
use Tests\Helpers\Financial\AccountHelpers;

use App\Jobs\Financial\UpdateAccountBalance;
use App\Models\Financial\Exceptions\InvalidTransactionAmountException;
use App\Models\Financial\Exceptions\Account\InsufficientFundsException;
use App\Models\Financial\Exceptions\Account\TransactionNotAllowedException;
use App\Models\Financial\Wallet;
use Tests\traits\Financial\NoHoldPeriod;

/**
 * Unit Tests for the `App\Models\Financial\Account` Model
 *
 * @group unit
 * @group financial
 * @group financial-account
 *
 * @group regression-financial
 *
 * @package Tests\Unit\Financial
 */
class AccountModelTest extends TestCase
{
    use NoHoldPeriod;

    /**
     * Internal wallet account is created correctly
     */
    public function test_create_user_wallet_account()
    {
        $user = User::factory()->create();

        $account = $user->createWalletAccount($this->defaultSystem, $this->defaultCurrency);

        $this->assertInstanceOf(Account::class, $account);
        $this->assertDatabaseHas($this->tableNames['account'], [
            'id' => $account->id,
            'owner_id' => $user->id,
            'type' => AccountTypeEnum::INTERNAL,
            'resource_type' => Wallet::getMorphStringStatic(),
        ], $this->getConnectionString());
    }

    /**
     * User's Internal account is created and retrieved when using getWalletAccount()
     * @depends test_create_user_wallet_account
     */
    public function test_get_user_wallet_account()
    {
        $user = User::factory()->create();

        // Test when user doesnt have internal account
        $account = $user->getWalletAccount($this->defaultSystem, $this->defaultCurrency);

        $this->assertInstanceOf(Account::class, $account);
        $this->assertDatabaseHas($this->tableNames['account'], [
            'id' => $account->id,
            'owner_id' => $user->id,
            'type' => AccountTypeEnum::INTERNAL,
            'resource_type' => Wallet::getMorphStringStatic(),
        ], $this->getConnectionString());

        $accountId = $account->id;

        unset($account);

        $account = $user->getWalletAccount($this->defaultSystem, $this->defaultCurrency);
        $this->assertInstanceOf(Account::class, $account);
        $this->assertEquals($accountId, $account->id);
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
     * Move funds from in account -> wallet account
     * @depends test_get_user_wallet_account
     */
    public function test_move_to_wallet_account_from_in_account()
    {
        $inAccount = Account::factory()->asIn()->create();
        $user = $inAccount->owner;

        $inAccount->moveToWallet(100);

        $this->assertHasBalanceOf(-100, $inAccount);

        // Created missing wallet account
        $this->assertDatabaseHas($this->tableNames['account'], [
            'owner_id' => $user->id,
            'type' => AccountTypeEnum::INTERNAL,
            'resource_type' => Wallet::getMorphStringStatic(),
        ], $this->getConnectionString());

        $walletAccount = $user->getWalletAccount($this->defaultSystem, $this->defaultCurrency);

        // Has from transaction
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $inAccount->getKey(),
            'credit_amount' => 0,
            'debit_amount' => 100,
            'currency' => $this->defaultCurrency,
        ], $this->getConnectionString());

        // Has to transaction
        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $walletAccount->getKey(),
            'credit_amount' => 100,
            'debit_amount' => 0,
            'currency' => $this->defaultCurrency,
        ], $this->getConnectionString());

        $fromTransaction = Transaction::where('account_id', $inAccount->getKey())->latest()->first();
        $toTransaction = Transaction::where('account_id', $walletAccount->getKey())->latest()->first();

        // Correct reference ids
        $this->assertEquals($fromTransaction->id, $toTransaction->reference_id);
        $this->assertEquals($toTransaction->id, $fromTransaction->reference_id);
    }

    /**
     * Internal accounts need to have enough balance
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

        $account->moveTo($toAccount, 300, [ 'type' => TransactionTypeEnum::SALE ]);

        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $account->getKey(),
            'credit_amount' => 0,
            'debit_amount' => 300,
            'currency' => $this->defaultCurrency,
        ], $this->getConnectionString());

        $this->assertDatabaseHas($this->tableNames['transaction'], [
            'account_id' => $toAccount->getKey(),
            'credit_amount' => 300,
            'debit_amount' => 0,
            'currency' => $this->defaultCurrency,
        ], $this->getConnectionString());

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
        $inAccount->moveToWallet(100);
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
     */
    public function test_settle_account_balance()
    {
        Event::fake([ UpdateAccountBalance::class ]);
        $items = AccountHelpers::loadWallet(1000);
        $this->assertHasBalanceOf(-1000, $items['in']);
        $this->assertHasBalanceOf(1000, $items['internal']);
        $creator = Account::factory()->asEarnings(User::factory()->create())->create();
        $items['internal']->moveTo($creator, 1000, [ 'type' => TransactionTypeEnum::SALE ]);
        AccountHelpers::settleAccounts([$items['in'], $items['internal'], $creator]);

        $this->assertHasBalanceOf(-1000, $items['in']);
        $this->assertHasBalanceOf(0, $items['internal']);
        $this->assertHasBalanceOf(650, $creator);
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
        ], $this->getConnectionString());

        $systemOwner = SystemOwner::where('name', 'platformFees')->where('system', $this->defaultSystem)->first();

        // Account was created
        $this->assertDatabaseHas($this->tableNames['account'], [
            'owner_id' => $systemOwner->getKey(),
            'type' => AccountTypeEnum::INTERNAL,
        ], $this->getConnectionString());
    }
}
