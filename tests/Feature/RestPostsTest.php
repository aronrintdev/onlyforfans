<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use DB;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;
use App\Enums\PostTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Fanledger;
use App\Post;
use App\Timeline;
use App\User;

class RestPostsTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /**
     *  @group posts
     *  @group regression
     */
    // %TODO: filters, timelines (see posts I follow), etc
    public function test_can_index_posts()
    {
        //$this->seed(\Database\Seeders\TestDatabaseSeeder::class);
        $timeline = Timeline::has('posts','>=',1)->first(); 
        $creator = $timeline->user;

        $response = $this->actingAs($creator)->ajaxJSON('GET', route('posts.index'));
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->posts);
        $postsR = collect($content->posts);
        //dd($postsR, $postsR[0]);
        $this->assertGreaterThan(0, $postsR->count());
        //$this->assertNotNull($postsR[0]->description);
        //$this->assertNotNull($postsR[0]->timeline_id);
        $this->assertObjectHasAttribute('timeline_id', $postsR[0]);
        $this->assertObjectHasAttribute('description', $postsR[0]);
        $this->assertEquals($postsR[0]->timeline_id, $timeline->id);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_can_show_my_post()
    {
        $timeline = Timeline::has('posts','>=',1)->first();
        $creator = $timeline->user;
        $post = $timeline->posts[0];

        $response = $this->actingAs($creator)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $postR = $content->post;
        $this->assertNotNull($postR->description);
        $this->assertNotNull($postR->timeline_id);
        $this->assertEquals($postR->timeline_id, $timeline->id);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_can_show_followed_timelines_post()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $creator = $timeline->user;
        $post = $timeline->posts[0];
        $fan = $timeline->followers[0];

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $postR = $content->post;
        $this->assertObjectHasAttribute('timeline_id', $postR);
        $this->assertEquals($postR->timeline_id, $timeline->id);

        // %TODO: test can not show unfollowed timeline's post
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_can_store_post_on_own_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->first();
        $creator = $timeline->user;

        $payload = [
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $postR = $content->post;
        $this->assertNotNull($postR->description);
        $this->assertEquals($payload['description'], $postR->description);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_can_update_own_post()
    {
        $timeline = Timeline::has('posts','>=',1)->first(); 
        $creator = $timeline->user;
        $post = $timeline->posts[0];

        $payload = [
            'description' => 'updated text',
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('posts.update', $post->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $postR = $content->post;
        $this->assertNotNull($postR->description);
        $this->assertEquals($payload['description'], $postR->description);
    }

    /**
     *  @group posts
     *  @group regression
     *  @group todo
     */
    public function test_can_store_post_on_followed_timeline()
    {
        // post on followed timeline (not my own)
        //$response->assertStatus(200);
    }

    /**
     *  @group posts
     *  @group regression
     *  @group todo
     */
    public function test_can_store_post_with_image_on_followed_timeline()
    {
        // post on followed timeline (not my own)
        //$response->assertStatus(200);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_can_destroy_own_post()
    {
        $timeline = Timeline::has('posts','>=',1)->first();
        $creator = $timeline->user;

        // First create a post (so it doesn't have any relations preventing it from being deleted)
        //$post = $timeline->posts[0];
        $payload = [
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $postR = $content->post;

        $response = $this->actingAs($creator)->ajaxJSON('DELETE', route('posts.destroy', $postR->id));
        $response->assertStatus(200);

        $response = $this->actingAs($creator)->ajaxJSON('GET', route('posts.show', $postR->id));
        $response->assertStatus(404);
    }

    /**
     *  @group posts
     *  @group regression
     *  @group this
     */
    public function test_timeline_follower_can_like_then_unlike_post()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $creator = $timeline->user;
        $post = $timeline->posts[0];
        $fan = $timeline->followers[0];

        // remove any existing likes by fan...
        DB::table('likeables')
            ->where('likee_id', $fan->id)
            ->where('likeable_type', 'posts')
            ->where('likeable_id', $post->id)
            ->delete();

        // LIKE the post
        $payload = [
            'likeable_type' => 'posts',
            'likeable_id' => $post->id,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('likeables.update', $fan->id), $payload); // fan->likee
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->likeable);
        $postR = $content->likeable;
        //$this->assertInstanceOf(Post::class, $postR);
        $this->assertEquals($post->id, $postR->id);

        $likeable = DB::table('likeables')
            ->where('likee_id', $fan->id)
            ->where('likeable_type', 'posts')
            ->where('likeable_id', $postR->id)
            ->first();
        $this->assertNotNull($likeable);
        $this->assertEquals($fan->id, $likeable->likee_id);
        $this->assertEquals('posts', $likeable->likeable_type);
        $this->assertEquals($postR->id, $likeable->likeable_id);

        // UNLIKE the post
        $payload = [
            'likeable_type' => 'posts',
            'likeable_id' => $post->id,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('DELETE', route('likeables.destroy', $fan->id), $payload); // fan->likee
        $response->assertStatus(200);

        $likeable = DB::table('likeables')
            ->where('likee_id', $fan->id)
            ->where('likeable_type', 'posts')
            ->where('likeable_id', $postR->id)
            ->first();
        $this->assertNull($likeable);
    }

    /**
     *  @group posts
     *  @group regression
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
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('posts.tip', $post->id), $payload);

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
     *  @group posts
     *  @group regression
     */
    public function test_can_purchase_post()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $creator = $timeline->user;
        $fan = $timeline->followers[0];
        $post = $timeline->posts[0];

        // Make sure post is 'priced'
        $post->type = PostTypeEnum::PRICED;
        $post->price = $this->faker->randomNumber(3);
        $post->save();

        $this->assertNotNull($post);

        // Check access (before: should be denied)
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(403);

        $payload = [
            'sharee_id' => $fan->id,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('posts.purchase', $post->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $postR = $content->post;

        // Check ledger
        $fanledger = Fanledger::where('fltype', PaymentTypeEnum::PURCHASE)->latest()->first();
        $this->assertNotNull($fanledger);
        $this->assertEquals(1, $fanledger->qty);
        $this->assertEquals($post->price*100, $fanledger->base_unit_cost_in_cents);
        $this->assertEquals(PaymentTypeEnum::PURCHASE, $fanledger->fltype);
        $this->assertEquals($fan->id, $fanledger->purchaser_id);
        $this->assertEquals($creator->id, $fanledger->seller_id);
        $this->assertEquals('posts', $fanledger->purchaseable_type);
        $this->assertEquals($post->id, $fanledger->purchaseable_id);
        $this->assertTrue( $post->sharees->contains( $fan->id ) );
        $this->assertTrue( $post->ledgersales->contains( $fanledger->id ) );
        $this->assertTrue( $fan->ledgerpurchases->contains( $fanledger->id ) );

        // Check access (after: should be allowed)
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);
    }

    /**
     *  @group TODO-posts
     */
    public function test_can_share_post()
    {
    }

    /**
     *  @group TODO-posts
     */
    public function test_can_pin_post()
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

