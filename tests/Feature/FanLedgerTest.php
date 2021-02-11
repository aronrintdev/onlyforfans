<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

use App\Enums\PaymentTypeEnum;
use App\Models\FanLedger;
use App\Models\Post;
use App\Models\Timeline;
use App\Models\User;

class FanLedgerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /**
     *  @group TODO-fanLedgers
     */
    public function test_can_index_fan_ledgers()
    {
    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
        $this->seed(TestDatabaseSeeder::class);
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

