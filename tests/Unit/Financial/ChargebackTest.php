<?php

namespace Tests\Unit\Financial;

use App\Enums\Financial\TransactionTypeEnum;
use App\Models\Financial\Account;
use Illuminate\Support\Facades\Event;
use App\Jobs\Financial\UpdateAccountBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        $this->markTestIncomplete();
    }

    public function test_account_transaction_mismatch_exception_works()
    {
        $this->markTestIncomplete();
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
        // Settle all balances
        $inAccount->settleBalance();
        $inAccount->save();
        $internalAccount->settleBalance();
        $internalAccount->save();
        $creatorAccount->settleBalance();
        $creatorAccount->save();

        dump($chargebackTransactions);

        $this->assertCurrencyAmountIsEqual(0, $inAccount->balance, 'In account balance back at zero');
        $this->assertCurrencyAmountIsEqual(0, $internalAccount->balance, 'Internal account balance back at zero');
        $this->assertCurrencyAmountIsEqual(0, $creatorAccount->balance, 'Creator account balance back at zero');

        $this->markTestIncomplete();
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
