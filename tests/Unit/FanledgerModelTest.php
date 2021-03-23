<?php
namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use DB;
use Database\Seeders\TestDatabaseSeeder;
use App\Models\Fanledger;

class FanledgerModelTest extends TestCase
{

    use RefreshDatabase, WithFaker;

    /**
     * @group fanledger-model
     * @group OFF-regression
     * @group erik
     */
    public function test_ledger_should_have_from_and_to_account_relations()
    {
        $ledgers = Fanledger::take(5)->get();
        $this->assertNotNull($ledgers);
        $this->assertGreaterThan(0, $ledgers->count());
        $this->assertNotNull($ledgers[0]->from_account);
        $this->assertNotNull($ledgers[0]->to_account);
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
