<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use DB;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;
use App\Models\Fanledger;
use App\Models\Mediafile;
use App\Models\Post;
use App\Models\Timeline;
use App\Models\User;
use App\Enums\MediafileTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Enums\PostTypeEnum;

class RestPostsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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
        $this->assertObjectHasAttribute('postable_id', $postsR[0]);
        $this->assertObjectHasAttribute('postable_type', $postsR[0]);
        $this->assertObjectHasAttribute('description', $postsR[0]);
        $this->assertEquals($postsR[0]->postable_type, 'timelines');
        $this->assertEquals($postsR[0]->postable_id, $timeline->id);
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
        $this->assertNotNull($postR->postable_id);
        $this->assertEquals($postR->postable_id, $timeline->id);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_can_show_followed_timelines_post()
    {
        //$timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $timeline = Timeline::whereHas('posts', function($q1) {
            $q1->where('type', PostTypeEnum::FREE);
        })->has('followers','>=',1)->first();
        $creator = $timeline->user;
        $post = $timeline->posts()->where('type', PostTypeEnum::FREE)->first();
        $fan = $timeline->followers[0];

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $postR = $content->post;
        $this->assertObjectHasAttribute('postable_id', $postR);
        $this->assertEquals($postR->postable_id, $timeline->id);

        // %TODO: test can not show unfollowed timeline's post
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_can_store_text_only_post_on_my_timeline()
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
     *  @group here
     */
    public function test_can_store_post_with_single_image_file_on_my_timeline()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('posts','>=',1)->first();
        $creator = $timeline->user;

        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $payload = [
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $postR = $content->post;

        // --

        $payload = [
            'mftype' => MediafileTypeEnum::POST,
            'mediafile' => $file,
            'resource_type' => 'posts',
            'resource_id' => $postR->id,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);

        $mediafile = Mediafile::where('resource_type', 'posts')->where('resource_id', $postR->id)->first();
        $this->assertNotNull($mediafile);
        Storage::disk('s3')->assertExists($mediafile->filename);
        $this->assertSame($filename, $mediafile->mfname);
        $this->assertSame(MediafileTypeEnum::POST, $mediafile->mftype);

        // Test relations
        $post = Post::find($postR->id);
        $this->assertNotNull($post);
        $this->assertTrue( $post->mediafiles->contains($mediafile->id) );
        $this->assertEquals( $post->id, $mediafile->resource->id );

    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_follower_can_view_free_post_on_my_timeline()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->firstOrFail();
        $creator = $timeline->user;
        $post = $timeline->posts->where('type', PostTypeEnum::FREE)->first();
        $fan = $timeline->followers[0];
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_follower_can_view_image_of_free_post_on_my_timeline()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;

        // --- Create a free post with an image ---

        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);
        $payload = [
            'type' => PostTypeEnum::FREE,
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $postR = $content->post;
        $payload = [
            'mftype' => MediafileTypeEnum::POST,
            'mediafile' => $file,
            'resource_type' => 'posts',
            'resource_id' => $postR->id,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);

        // --

        $timeline->refresh();
        $creator->refresh();
        $fan = $timeline->followers[0];
        $post = $timeline->posts()->has('mediafiles', '>=', 1)
                         ->where('type', PostTypeEnum::FREE)
                         ->firstOrFail();
        $mediafile = $post->mediafiles->shift();

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('mediafiles.show', $mediafile->id));
        $response->assertStatus(200);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_non_follower_can_not_view_image_of_free_post_on_my_timeline()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;

        // --- Create a free post with an image ---

        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);
        $payload = [
            'type' => PostTypeEnum::FREE,
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $postR = $content->post;
        $payload = [
            'mftype' => MediafileTypeEnum::POST,
            'mediafile' => $file,
            'resource_type' => 'posts',
            'resource_id' => $postR->id,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);

        // --

        $timeline->refresh();
        $creator->refresh();
        $nonFan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', '<>', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        //$this->assertNotEquals($nonFan->id, $creator->id);
        //$this->assertFalse($timeline->followers->contains($nonFan->id));

        $post = $timeline->posts()->has('mediafiles', '>=', 1)
                         ->where('type', PostTypeEnum::FREE)
                         ->firstOrFail();
        $mediafile = $post->mediafiles->shift();

        $response = $this->actingAs($nonFan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(403);

        $response = $this->actingAs($nonFan)->ajaxJSON('GET', route('mediafiles.show', $mediafile->id));
        $response->assertStatus(403);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_nonowner_can_not_store_post_on_my_timeline()
    {
        $timeline = Timeline::has('posts', '>=', 1)->first();
        $creator = $timeline->user;
        $nonowner = User::where('id', '<>', $creator->id)->first();

        $payload = [
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($nonowner)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(403);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_can_update_my_post()
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
     *  @group OFF-regression
     *  @group todo
     */
    public function test_can_store_post_on_followed_timeline()
    {
        // post on followed timeline (not my own)
        //$response->assertStatus(200);
    }

    /**
     *  @group posts
     *  @group OFF-regression
     *  @group TODO
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
    public function test_can_destroy_my_post()
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
     */
    public function test_timeline_follower_can_like_then_unlike_post()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->firstOrFail();
        $creator = $timeline->user;
        $post = $timeline->posts->where('type', PostTypeEnum::FREE)->first();
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
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::PRICED);
            })->firstOrFail();
        $creator = $timeline->user;
        $fan = $timeline->followers[0];
        $post = $timeline->posts->where('type', PostTypeEnum::PRICED)->first();

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

