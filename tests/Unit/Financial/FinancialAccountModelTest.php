<?php

namespace Tests\Unit\Financial;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\User;
use App\Models\Financial\Account;
use Illuminate\Support\Facades\Config;
use App\Enums\Financial\AccountTypeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Financial\Exceptions\InvalidTransactionAmountException;
use App\Models\Financial\Transaction;

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
        $this->defaultCurrency = Config::get('transactions.systems.' . $this->defaultSystem . '.defaults.currency');
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
        $user = User::factory()->create();
        $inAccount = Account::create([
            'system' => $this->defaultSystem,
            'owner_type' => $user->getMorphString(),
            'owner_id' => $user->getKey(),
            'name' => $user->username . ' In Account',
            'type' => AccountTypeEnum::IN,
            'currency' => $this->defaultCurrency,
            'balance' => 0,
            'balance_last_updated_at' => Carbon::now(),
            'pending' => 0,
            'pending_last_updated_at' => Carbon::now(),
        ]);

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
    public function test_block_account()
    {
        // TODO: Implement
        $this->markTestIncomplete();
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
