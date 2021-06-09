<?php

namespace Tests\Feature\Financial;

use App\Models\Financial\Account;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\Financial\AccountHelpers;
use Tests\traits\Financial\NoHoldPeriod;

/**
 * @group payouts
 * @group financial
 *
 * @package Tests\Feature\Financial
 */
class PayoutGatewayTest extends TestCase
{
    use RefreshDatabase, WithFaker, NoHoldPeriod;

    public function test_can_request_a_payout()
    {
        // Seed
        $inAccount = Account::factory()->asIn()->create();
        $inAccount->moveToInternal(10000);
        $this->markTestIncomplete();
    }

}