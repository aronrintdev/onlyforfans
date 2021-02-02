<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use DB;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;
use App\Enums\PostTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Post;
use App\Timeline;
use App\User;

class RestPostsTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /**
     *  @group group_posts
     *  @group group_regression
     */
    // %TODO: filters, timelines (see posts I follow), etc
    public function test_can_index_posts()
    {
        //$this->seed(\Database\Seeders\TestDatabaseSeeder::class);
        $timeline = Timeline::has('posts','>=',1)->first(); // assume non-admin (%FIXME)
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
     *  @group group_posts
     *  @group group_regression
     */
    public function test_can_show_my_post()
    {
        $timeline = Timeline::has('posts','>=',1)->first(); // assume non-admin (%FIXME)
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
     *  @group group_posts
     *  @group group_regression
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
     *  @group group_posts
     *  @group group_regression
     */
    public function test_can_store_post_on_own_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->first(); // assume non-admin (%FIXME)
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
     *  @group group_posts
     *  @group group_regression
     */
    public function test_can_update_own_post()
    {
        $timeline = Timeline::has('posts','>=',1)->first(); // assume non-admin (%FIXME)
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
     *  @group group_posts
     *  @group group_regression
     */
    public function test_can_destroy_own_post()
    {
        $timeline = Timeline::has('posts','>=',1)->first(); // assume non-admin (%FIXME)
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
     *  @group group_posts
     *  @group group_regression
     */
    public function test_timeline_follower_can_like_then_unlike_post()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $creator = $timeline->user;
        $post = $timeline->posts[0];
        $fan = $timeline->followers[0];

        // remove any existing likes by fan...
        $likeable = DB::table('likeables')
            ->where('likee_id', $fan->id)
            ->where('likeable_type', 'posts')
            ->where('likeable_id', $post->id)
            ->first();
        if ($likeable) {
            $likeable->delete();
        }
        unset($likeable);

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
     *  @group TODO-group_posts
     */
    public function test_can_share_post()
    {
    }

    /**
     *  @group TODO-group_posts
     */
    public function test_can_purchase_post()
    {
    }

    /**
     *  @group TODO-group_posts
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

