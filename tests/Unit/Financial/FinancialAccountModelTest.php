<?php

namespace Tests\Unit\Financial;

use App\Enums\Financial\AccountTypeEnum;
use App\Models\User;
use App\Models\Financial\Account;
use App\Models\Financial\Transaction;
use App\Models\Financial\Exceptions\InvalidTransactionAmountException;
use App\Models\Financial\Exceptions\Account\InsufficientFundsException;
use App\Models\Financial\Exceptions\Account\TransactionNotAllowedException;

use Carbon\Carbon;

use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

use Tests\TestCase;

/**
 * Unit Tests for the `App\Models\Financial\Account` Model
 */
class FinancialAccountModelTest extends TestCase
{
    use RefreshDatabase;

    protected $defaultSystem = null;
    protected $defaultCurrency = null;
    protected $accountTableName = null;
    protected $transactionTableName = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->defaultSystem = Config::get('transactions.default');
        $this->defaultCurrency = Config::get('transactions.defaultCurrency');
        $this->accountTableName = app(Account::class)->getTable();
        $this->transactionTableName = app(Transaction::class)->getTable();
    }

    /**
     * @group unit
     * @group financial
     */
    public function test_create_in_account()
    {
        // TODO: Implement
        $this->markTestIncomplete();
    }

    /**
     * @group unit
     * @group financial
     */
    public function test_create_user_internal_account()
    {
        $user = User::factory()->create();

        $account = $user->createInternalAccount($this->defaultSystem, $this->defaultCurrency);

        $this->assertInstanceOf(Account::class, $account);
        $this->assertDatabaseHas($this->accountTableName, [
            'id' => $account->id,
            'owner_id' => $user->id,
            'type' => AccountTypeEnum::INTERNAL
        ]);
    }

    /**
     * @group unit
     * @group financial
     */
    public function test_get_user_internal_account()
    {
        $user = User::factory()->create();

        // Test when user doesnt have internal account
        $account = $user->getInternalAccount($this->defaultSystem, $this->defaultCurrency);

        $this->assertInstanceOf(Account::class, $account);
        $this->assertDatabaseHas($this->accountTableName, [
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
     * @group unit
     * @group financial
     */
    public function test_create_out_account()
    {
        // TODO: Implement
        $this->markTestIncomplete();
    }

    /**
     * @group unit
     * @group financial
     */
    public function test_move_to_internal_account_from_in_account()
    {
        $inAccount = Account::factory()->asTypeIn()->create();
        $user = $inAccount->owner;

        $this->expectException(InvalidTransactionAmountException::class);
        $inAccount->moveToInternal(-1);

        $this->expectException(InvalidTransactionAmountException::class);
        $inAccount->moveToInternal(0);

        $inAccount->moveToInternal(100);

        // Created missing Internal account
        $this->assertDatabaseHas($this->accountTableName, [
            'owner_id' => $user->id,
            'type' => AccountTypeEnum::INTERNAL
        ]);

        $internalAccount = $user->getInternalAccount($this->defaultSystem, $this->defaultCurrency);

        // Has from transaction
        $this->assertDatabaseHas($this->transactionTableName, [
            'account_id' => $inAccount->getKey(),
            'credit_amount' => 0,
            'debit_amount' => 100,
            'currency' => $this->defaultCurrency,
        ]);

        // Has to transaction
        $this->assertDatabaseHas($this->transactionTableName, [
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
     * @group unit
     * @group financial
     */
    public function test_move_to_internal_account_from_internal_account()
    {
        Queue::fake();

        $account = Account::factory()->asTypeInternal()->withBalance(1000)->create();
        $toAccount = Account::factory()->asTypeInternal()->withBalance(0)->create();

        $this->expectException(InsufficientFundsException::class);
        $account->moveTo($toAccount, 20000);

        $account->moveTo($toAccount, 300);
        $this->assertDatabaseHas($this->transactionTableName, [
            'account_id' => $account->getKey(),
            'credit_amount' => 0,
            'debit_amount' => 300,
            'currency' => $this->defaultCurrency,
        ]);
        $this->assertDatabaseHas($this->transactionTableName, [
            'account_id' => $toAccount->getKey(),
            'credit_amount' => 300,
            'debit_amount' => 0,
            'currency' => $this->defaultCurrency,
        ]);

        $account->fresh();
        $this->assertEquals(700, $account->balance);

        Queue::assertPushed(UpdateAccountBalance::class);
    }


    /**
     * @group unit
     * @group financial
     */
    public function test_blocked_account_cannot_make_transactions()
    {
        $inAccount = Account::factory()->asTypeIn()->transactionsBlocked()->create();

        $this->expectException(TransactionNotAllowedException::class);
        $inAccount->moveToInternal(100);

        $blockedAccount1 = Account::factory()
            ->asInternal()
            ->withBalance(200)
            ->transactionsBlocked()
            ->create();
        $blockedAccount2 = Account::factory()
            ->asInternal()
            ->withBalance(200)
            ->transactionsBlocked()
            ->create();
        $unblockedAccount = Account::factory()
            ->asInternal()
            ->withBalance(200)
            ->transactionsAllowed()
            ->create();

        $this->expectException(TransactionNotAllowedException::class);
        $blockedAccount1->moveTo($blockedAccount2, 100);

        $this->expectException(TransactionNotAllowedException::class);
        $blockedAccount1->moveTo($unblockedAccount, 100);

        $this->expectException(TransactionNotAllowedException::class);
        $unblockedAccount->moveTo($blockedAccount1, 100);
    }

    /**
     * @group unit
     * @group financial
     */
    public function test_verify_account()
    {
        // TODO: Implement
        $this->markTestIncomplete();
    }

    /**
     * @group unit
     * @group financial
     */
    public function test_access_system_account()
    {
        // TODO: Implement
        $this->markTestIncomplete();
    }
}
