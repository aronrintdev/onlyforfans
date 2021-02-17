<?php
namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use DB;
use App\Models\Fanledger;

class FanledgerModelTest extends TestCase
{

    /**
     * @group fanledger-model
     * @group here
     * @group erik
     */
    public function test_ledger_should_have_from_and_to_account_relations()
    {
        $ledger = Ledger::first();
        $this->assertNotNull($ledger->from_account);
        $this->assertNotNull($ledger->to_account);
    }

}
