<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

use App\Enums\PaymentTypeEnum;
use App\Fanledger;
use App\Post;
use App\Timeline;
use App\User;

class FanledgerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /**
     *  @group TODO-fanledgers
     */
    public function test_can_index_fanledgers()
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

