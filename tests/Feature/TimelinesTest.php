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

class TimelinesTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_can_send_tip_to_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $creator = $timeline->user;
        $fan = $timeline->followers[0];

        $payload = [
            'base_unit_cost_in_cents' => $this->faker->randomNumber(3),
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.tip', $timeline->id), $payload);

        $response->assertStatus(200);

        $content = json_decode($response->content());
        $timelineR = $content->timeline;

        $fanledger = Fanledger::where('fltype', PaymentTypeEnum::TIP)
            ->where('purchaseable_type', 'timelines')
            ->where('purchaseable_id', $timeline->id)
            ->where('seller_id', $creator->id)
            ->where('purchaser_id', $fan->id)
            ->first();
        $this->assertNotNull($fanledger);
        $this->assertEquals(1, $fanledger->qty);
        $this->assertEquals($payload['base_unit_cost_in_cents'], $fanledger->base_unit_cost_in_cents);
        $this->assertTrue( $timeline->ledgerSales->contains( $fanledger->id ) );
        $this->assertTrue( $fan->ledgerPurchases->contains( $fanledger->id ) );
    }

    /**
     *  @group shareables
     *  @group regression
     */
    public function test_can_follow_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first();
        $creator = $timeline->user;

        // find a user who is not yet a follower (includes subscribers) of timeline
        $fan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', '<>', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        $payload = [
            'sharee_id' => $fan->id,
            'notes'=>'test_can_subscribe_to_timeline',
        ];
        //$response = $this->actingAs($fan)->ajaxJSON('PUT', route('shareables.followTimeline', $timeline->id), $payload);
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.follow', $timeline->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $timelineR = $content->timeline;

        $this->assertNotNull($timelineR);
        $this->assertEquals($timeline->id, $timelineR->id);

        $timeline->refresh();
        $this->assertEquals('default', $timeline->followers->find($fan->id)->pivot->access_level);
        $this->assertEquals('timelines', $timeline->followers->find($fan->id)->pivot->shareable_type);
        $this->assertTrue( $timeline->followers->contains( $fan->id ) );
        $this->assertTrue( $fan->followedtimelines->contains( $timelineR->id ) );

        // %TODO: unfollow
    }

    /**
     *  @group shareables
     *  @group regression
     */
    public function test_can_subscribe_to_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // includes subscribers
        $creator = $timeline->user;

        // Make sure creator's timeline is paid-only
        $creator->is_follow_for_free = false;
        $creator->price = $this->faker->randomNumber(3);
        $creator->save();
        $timeline->refresh();

        // find a user who is not yet a follower (includes subscribers) of timeline
        $fan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', '<>', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        // Check access (before: should be denied)
        // [ ] %TODO: actually this is more complex: they can access the timeline, but can only see a subset
        //     of posts on it before subscription
        //$response = $this->actingAs($fan)->ajaxJSON('GET', route('timelines.show', $timeline->id));
        //$response->assertStatus(403);

        $payload = [
            'sharee_id' => $fan->id,
            'notes'=>'test_can_subscribe_to_timeline',
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.subscribe', $timeline->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $timelineR = $content->timeline;

        $this->assertNotNull($timelineR);
        $this->assertEquals($timeline->id, $timelineR->id);

        $timeline->refresh();
        $this->assertEquals('premium', $timeline->followers->find($fan->id)->pivot->access_level);
        $this->assertEquals('timelines', $timeline->followers->find($fan->id)->pivot->shareable_type);
        $this->assertTrue( $timeline->followers->contains( $fan->id ) );
        $this->assertTrue( $fan->followedtimelines->contains( $timelineR->id ) );

        // Check ledger
        $fanledger = Fanledger::where('fltype', PaymentTypeEnum::SUBSCRIPTION)
            ->where('purchaseable_type', 'timelines')
            ->where('purchaseable_id', $timeline->id)
            ->where('seller_id', $creator->id)
            ->where('purchaser_id', $fan->id)
            ->first();
        $this->assertNotNull($fanledger);
        $this->assertEquals(1, $fanledger->qty);
        $this->assertEquals(intval($timeline->user->price*100), $fanledger->base_unit_cost_in_cents);
        $this->assertTrue( $timeline->ledgerSales->contains( $fanledger->id ) );
        $this->assertTrue( $fan->ledgerPurchases->contains( $fanledger->id ) );

        // Check access (after: should be allowed)
        //$response = $this->actingAs($fan)->ajaxJSON('GET', route('timelines.show', $timeline->id));
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('timelines.show', $timeline->username));
        $response->assertStatus(200);

        // %TODO: unsubscribe (will not be charged next recurring payment period)
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

