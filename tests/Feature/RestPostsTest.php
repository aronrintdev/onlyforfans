<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
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
     *  @group devpost
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
     *  @group devpost
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
     *  @group devpost
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
     *  @group devpost
     */
    public function test_can_store_post()
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
     *  @group devpost
     */
    public function test_can_update_post()
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
     *  @group devpost
     */
    public function test_can_destroy_post()
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
     *  @group devpost
     */
    // %TODO: unlike
    public function test_can_like_post()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $creator = $timeline->user;
        $post = $timeline->posts[0];
        $fan = $timeline->followers[0];

        // remove any existing likes...
        $likeable = DB::table('likeables')
            ->where('likee_id', $fan->id)
            ->where('likeable_type', 'posts')
            ->where('likeable_id', $post->id)
            ->first();
        if ($likeable) {
            $likeable->delete();
        }
        unset($likeable);

        $payload = [
            'likeable_type' => 'posts',
            'likeable_id' => $post->id,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('likeables.update', $fan->id), $payload);
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
    }

    /**
     *  @group devpost
     */
    public function test_can_comment_on_post()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $creator = $timeline->user;
        $post = $timeline->posts[0];
        $fan = $timeline->followers[0];

        // remove any existing comments on post by fan...
        DB::table('comments')
            ->where('post_id', $post->id)
            ->where('user_id', $fan->id)
            ->delete();

        $post->refresh();
        $origCommentCount = $post->comments->count();

        $payload = [
            'post_id' => $post->id,
            'user_id' => $fan->id,
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('POST', route('comments.store', $fan->id), $payload);
        $response->assertStatus(201);
        $post->refresh();
        $post->load('comments');

        $content = json_decode($response->content());
        $this->assertObjectHasAttribute('comment', $content);
        $commentR = $content->comment;

        $this->assertNotNull('comments', $post);
        $this->assertGreaterThan(0, $post->comments->count());
        $this->assertEquals($origCommentCount+1, $post->comments->count());

        $commentsByFan = $post->comments->filter( function($v,$k) use($fan) {
            return $v->user_id === $fan->id;
        });
        //dd($commentsByFan);
        $this->assertEquals(1, $commentsByFan->count());
        $this->assertEquals($payload['description'], $commentsByFan[0]->description);
        $this->assertEquals($commentsByFan[0]->id, $commentR->id);
    }

    /**
     *  @group TODO-devpost
     */
    public function test_can_share_post()
    {
    }

    /**
     *  @group TODO-devpost
     */
    public function test_can_tip_post()
    {
    }

    /**
     *  @group TODO-devpost
     */
    public function test_can_purchase_post()
    {
    }

    /**
     *  @group TODO-devpost
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

