<?php
namespace Tests\Feature\Financial;

use Illuminate\Foundation\Testing\WithFaker;
use DB;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;
use App\Models\User;

class RestTransactionssTest extends TestCase
{
    use WithFaker;

    /**
     *  @group financial-transactions
     *  @group regression
     *  @group regression-financial
     *  @group here0726
     */
    public function test_admin_can_list_transactions()
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->ajaxJSON('GET', route('financial.transactions.index'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                0 => [
                    'id',
                    'account_id',
                    'credit_amount',
                    'debit_amount',
                    'currency',
                    'type',
                    'description',
                    'resource_type',
                    'resource_id',
                    'metadata',
                    'settled_at',
                    'created_at',
                    'purchaser' => [
                        'id',
                        'username',
                        'slug',
                        'name',
                        'avatar',
                    ],
                ],
            ],
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);

        $content = json_decode($response->content());
    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

