<?php
namespace Tests\Feature\Financial;

use Illuminate\Foundation\Testing\WithFaker;
use DB;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

class RestAccountsTest extends TestCase
{
    use WithFaker;

    /**
     *  @group financial-accounts
     *  @group regression
     *  @group regression-financial
     */
    public function test_admin_can_list_accounts()
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->ajaxJSON('GET', route('financial.accounts.index'));
        $admin->removeRole('admin'); // Must remove or following tests may fail (!)
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                0 => [
                    'id', 
                    'system', 
                    'owner_type', 
                    'owner_id', 
                    'name', 
                    'type', 
                    'currency', 
                    'balance', 
                    'balance_last_updated_at', 
                    'pending', 
                    'pending_last_updated_at', 
                    'resource_id', 
                    'resource_type', 
                    'verified', 
                    'can_make_transactions', 
                    'created_at', 
                ],
            ],
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);

        $content = json_decode($response->content());
        //dd($content);
        //$this->assertEquals(1, $content->meta->current_page);
        //$this->assertNotNull($content->data);
        //$this->assertGreaterThan(0, count($content->data));
        //$this->assertObjectHasAttribute('description', $content->data[0]);
        //collect($content->data)->each( function($c) use(&$creator) { // all belong to owner
            //$this->assertEquals($creator->id, $c->user_id);
        //});
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

