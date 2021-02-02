<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

//use App\Enums\PostTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Fanledger;
use App\Post;
use App\Timeline;
use App\User;

class FanledgerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /**
     *  @group group_fanledgers
     *  @group group_regression
     */
    public function test_can_send_tip_on_post()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $creator = $timeline->user;
        $post = $timeline->posts[0];
        $fan = $timeline->followers[0];

        $payload = [
            'base_unit_cost_in_cents' => $this->faker->randomNumber(3),
        ];
        $response = $this->actingAs($fan)->ajaxJSON('POST', route('posts.tip', $post->id), $payload);

        $response->assertStatus(200);

        $content = json_decode($response->content());
        $postR = $content->post;

        $fanledger = Fanledger::where('fltype', PaymentTypeEnum::TIP)->latest()->first();
        $this->assertNotNull($fanledger);
        $this->assertEquals(1, $fanledger->qty);
        $this->assertEquals($payload['base_unit_cost_in_cents'], $fanledger->base_unit_cost_in_cents);
        $this->assertEquals(PaymentTypeEnum::TIP, $fanledger->fltype);
        $this->assertEquals($fan->id, $fanledger->purchaser_id);
        $this->assertEquals($creator->id, $fanledger->seller_id);
        $this->assertEquals('posts', $fanledger->purchaseable_type);
        $this->assertEquals($post->id, $fanledger->purchaseable_id);
        $this->assertTrue( $post->ledgersales->contains( $fanledger->id ) );
        $this->assertTrue( $fan->ledgerpurchases->contains( $fanledger->id ) );
    }

    /**
     *  @group group_fanledgers
     *  @group group_regression
     *  @group this
     */
    public function test_can_send_tip_to_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $creator = $timeline->user;
        $post = $timeline->posts[0];
        $fan = $timeline->followers[0];

        $payload = [
            'base_unit_cost_in_cents' => $this->faker->randomNumber(3),
        ];
        $response = $this->actingAs($fan)->ajaxJSON('POST', route('timelines.tip', $timeline->id), $payload);

        $response->assertStatus(200);

        $content = json_decode($response->content());
        $timelineR = $content->timeline;

        $fanledger = Fanledger::where('fltype', PaymentTypeEnum::TIP)->latest()->first();
        $this->assertNotNull($fanledger);
        $this->assertEquals(1, $fanledger->qty);
        $this->assertEquals($payload['base_unit_cost_in_cents'], $fanledger->base_unit_cost_in_cents);
        $this->assertEquals(PaymentTypeEnum::TIP, $fanledger->fltype);
        $this->assertEquals($fan->id, $fanledger->purchaser_id);
        $this->assertEquals($creator->id, $fanledger->seller_id);
        $this->assertEquals('timelines', $fanledger->purchaseable_type);
        $this->assertEquals($timeline->id, $fanledger->purchaseable_id);
        $this->assertTrue( $timeline->ledgersales->contains( $fanledger->id ) );
        $this->assertTrue( $fan->ledgerpurchases->contains( $fanledger->id ) );

    }

    /**
     *  @group TODO-group_fanledgers
     */
    public function test_can_purchase_post()
    {
        // [ ] %TODO: RBAC permissions to access post
        // [ ] %TODO: test ledgersales & ledgerpurchases relations
        $buyerUser = $this->buyerUser;
        $sellerUser = $this->sellerUser;
        $post = Post::where('timeline_id', $sellerUser->timeline->id)->where('type', PostTypeEnum::PRICED)->first();
        $this->assertNotNull( $post->price );
        $this->assertGreaterThan( 0, $post->price );

        $payload = [ ];
        $response = $this->actingAs($buyerUser)->ajaxJSON('POST', route('posts.purchase', $post->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $postR = $content->post;
        $this->assertEquals($post->id, $postR->id);

        $post->refresh();
        $buyerUser->refresh();
        $sellerUser->refresh();

        $this->assertEquals( 1, $post->ledgersales->count() );
        $this->assertEquals( PaymentTypeEnum::PURCHASE, $post->ledgersales[0]->fltype );
        $this->assertEquals( 'posts', $post->ledgersales[0]->purchaseable_type );

        $this->assertEquals( 1, $buyerUser->ledgerpurchases->count() );
        $this->assertEquals( $post->ledgersales[0]->id, $buyerUser->ledgerpurchases[0]->id );

        $this->assertTrue( $buyerUser->sharedPosts->contains('id', $post->id) ); 

        $fl = $post->ledgersales()->where('purchaser_id', $buyerUser->id)->first();
        $this->assertEquals( $post->id, $fl->purchaseable_id );
        $this->assertEquals( 1, $fl->qty );
        $this->assertEquals( $payload['amount'], $fl->total_amount ); // no taxes applied
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

