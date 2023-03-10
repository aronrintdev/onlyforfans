<?php

namespace Tests\Feature\Financial;

use Tests\TestCase;
use App\Models\User;
use App\Models\Financial\Account;
use App\Models\Financial\AchAccount;
use Tests\traits\Financial\NoHoldPeriod;
use Tests\Helpers\Financial\AccountHelpers;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * @group payouts
 * @group financial
 *
 * @package Tests\Feature\Financial
 */
class PayoutGatewayTest extends TestCase
{
    use WithFaker, NoHoldPeriod;

    protected $connectionName = 'financial';

    /**
     * Rest request of payout'
     *
     * @group regression
     * @group regression-financial
     */
    public function test_can_request_a_payout()
    {
        // Seed
        $inAccount = Account::factory()->asIn()->create();
        $inAccount->moveToWallet(10000);
        $wallet = $inAccount->getWalletAccount();

        $achAccount = AchAccount::factory()->forUser($inAccount->owner)->create();
        $earnings = $achAccount->account->getEarningsAccount();

        $wallet->moveTo($earnings, 10000, [ 'ignoreBalance' => true ]);

        $response = $this->actingAs($earnings->owner)->ajaxJSON('POST', route('payouts.request'), [
            'account_id' => $achAccount->account->getKey(),
            'amount'     => 10000,
            'currency'   => $achAccount->account->currency,
        ]);
        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);
    }

    /**
     * Rest request of payout
     *
     * @group regression
     * @group regression-financial
     */
    public function test_cannot_request_payout_higher_than_balance()
    {
        $inAccount = Account::factory()->asIn()->create();
        $inAccount->moveToWallet(10000);

        $achAccount = AchAccount::factory()->forUser($inAccount->owner)->create();

        $response = $this->actingAs($inAccount->owner)->ajaxJSON('POST', route('payouts.request'), [
            'account_id' => $achAccount->account->getKey(),
            'amount'     => 10001,
            'currency'   => $achAccount->account->currency,
        ]);
        $response->assertStatus(400);
    }

}
