<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

//use App\Enums\PostTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Models\Fanledger;
use App\Models\Post;
use App\Models\Timeline;
use App\Models\User;

class ShareablesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group TODO-shareables
     *  @group OFF-regression
     */
    public function test_can_list_shareables()
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

