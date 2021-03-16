<?php

namespace Tests\Unit\Financial;

use App\Models\User;
use App\Models\Financial\Account;
use App\Models\Financial\SystemOwner;
use App\Models\Financial\Transaction;
use Illuminate\Support\Facades\Queue;
use App\Enums\Financial\AccountTypeEnum;
use App\Jobs\Financial\UpdateAccountBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Financial\Exceptions\InvalidTransactionAmountException;
use App\Models\Financial\Exceptions\Account\InsufficientFundsException;
use App\Models\Financial\Exceptions\Account\TransactionNotAllowedException;
use Illuminate\Support\Facades\Event;

/**
 * Unit Tests for the `App\Models\Financial\Account` Model
 *
 * @group unit
 * @group financial
 * @group financial-account
 */
class AccountModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     *
     */
    public function test_create_in_account()
    {
        // TODO: Implement
        $this->markTestIncomplete();
    }

    /**
     *
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
     *
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
     *
     */
    public function test_create_out_account()
    {
        // TODO: Implement
        $this->markTestIncomplete();
    }

    /**
     *
     */
    public function test_move_negative_amount_fails()
    {
        $accounts = $this->createInternalAccounts([1000, 1000]);
        $this->expectException(InvalidTransactionAmountException::class);
        $accounts[0]->moveTo($accounts[1], -1);
    }

    public function test_move_zero_amount_fails()
    {
        $accounts = $this->createInternalAccounts([1000, 1000]);
        $this->expectException(InvalidTransactionAmountException::class);
        $accounts[0]->moveTo($accounts[1], 0);
    }

    /**
     *
     */
    public function test_move_to_internal_account_from_in_account()
    {
        $inAccount = Account::factory()->asIn()->create();
        $user = $inAccount->owner;

        $inAccount->moveToInternal(100);

        $this->assertCurrencyAmountIsEqual(-100, $inAccount->balance);

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
     *
     */
    public function test_move_with_insufficient_funds_fails()
    {
        $account = Account::factory()->asInternal()->withBalance(1000)->create();
        $toAccount = Account::factory()->asInternal()->withBalance(0)->create();

        $this->expectException(InsufficientFundsException::class);
        $account->moveTo($toAccount, 20000);
    }

    /**
     *
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

        $this->assertCurrencyAmountIsEqual(700, $account->balance);

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
     *
     */
    public function test_verify_account()
    {
        // TODO: Implement
        $this->markTestIncomplete();
    }

    /**
     *
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


    /**
     * Setup Helper | Create Internal Accounts
     */
    private function createInternalAccounts($balances = [])
    {
        $accounts = [];
        foreach ($balances as $balance) {
            array_push($accounts, Account::factory()->asInternal()->withBalance($balance)->create());
        }
        return $accounts;
    }
}
