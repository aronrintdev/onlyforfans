<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use DB;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;
use App\Models\Timeline;
use App\Models\User;
use App\Models\Subscription;
//use App\Enums\PostTypeEnum;

class RestSubscriptionsTest extends TestCase
{
    use WithFaker;

    /**
     *  @group subscriptions
     *  @group regression-base
     */
    public function test_owner_can_list_subscriptions()
    {
        $creator = User::has('subscriptions','>=',1)->firstOrFail();
        $timeline = $creator->timeline;

        $response = $this->actingAs($creator)->ajaxJSON('GET', route('subscriptions.index'), [
            //'user_id' => $creator->id,
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);

        $content = json_decode($response->content());
        //dd($content);
        $this->assertEquals(1, $content->meta->current_page);
        $this->assertNotNull($content->data);
        $this->assertGreaterThan(0, count($content->data));
        $this->assertObjectHasAttribute('user_id', $content->data[0]);
        $this->assertObjectHasAttribute('subscribable_type', $content->data[0]);
        $this->assertObjectHasAttribute('subscribable_id', $content->data[0]);
        $this->assertObjectHasAttribute('user_id', $content->data[0]);
        $this->assertObjectHasAttribute('period', $content->data[0]);
        $this->assertObjectHasAttribute('period_interval', $content->data[0]);
        $this->assertObjectHasAttribute('price', $content->data[0]);
        $this->assertObjectHasAttribute('currency', $content->data[0]);
        $this->assertObjectHasAttribute('access_level', $content->data[0]);
        $this->assertObjectHasAttribute('active', $content->data[0]);
        $this->assertObjectHasAttribute('canceled_at', $content->data[0]);

        // All resources returned are owned by creator
        $ownedByCreator = collect($content->data)->reduce( function($acc, $item) use(&$creator) {
            if ( $item->user_id === $creator->id ) {
                $acc += 1;
            }
            return $acc;
        }, 0);
        $this->assertEquals(count($content->data), $ownedByCreator); 
        /*
        $this->assertEquals($expectedCount, count($content->data));
         */
    }

    /**
     *  @group subscriptions
     *  @group regression
     *  @group regression-base
     */
    public function test_owner_can_list_active_subscriptions()
    {
        $creator = User::whereHas('subscriptions', function($query) {
            return $query->active();
        })->firstOrFail();

        $response = $this->actingAs($creator)->ajaxJSON('GET', route('subscriptions.index'), [
            'is_active' => 1,
        ]);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertEquals(1, $content->meta->current_page);
        $this->assertNotNull($content->data);
        $this->assertGreaterThan(0, count($content->data));

        // All resources returned are owned by creator
        $nonActive = collect($content->data)->reduce( function($acc, $item) use(&$creator) {
            if ( !$item->active) {
                $acc += 1;
            }
            return $acc;
        }, 0);
        $this->assertEquals(0, $nonActive);
    }

    /**
     *  @group subscriptions
     *  @group regression
     *  @group regression-base
     *  @group erik
     // Erik maybe call api to cancel a subscripion before testing for it (within the code below)
     */
    public function test_owner_can_list_inactive_subscriptions()
    {
        $creator = User::has('subscriptions','>=',1)->firstOrFail();
        $timeline = $creator->timeline;

        $response = $this->actingAs($creator)->ajaxJSON('GET', route('subscriptions.index'), [
            'is_active' => 0,
        ]);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertEquals(1, $content->meta->current_page);
        $this->assertNotNull($content->data);
        $this->assertGreaterThan(0, count($content->data));
        // All resources returned are owned by creator
        $nonActive = collect($content->data)->reduce( function($acc, $item) use(&$creator) {
            if ( $item->active) {
                $acc += 1;
            }
            return $acc;
        }, 0);
        $this->assertEquals(0, $nonActive); 
    }


    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
        //$this->seed(TestDatabaseSeeder::class);
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

