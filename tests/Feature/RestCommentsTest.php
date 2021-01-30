<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use DB;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;
use App\Comment;
use App\Post;
use App\Timeline;
use App\User;

class RestCommentsTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /**
     *  @group devcomment
     */
    // %TODO: filters, timelines (see comments I have valid access to), etc
    public function test_can_index_comments()
    {
        // should return only comments for which session user is the author

        //$this->seed(\Database\Seeders\TestDatabaseSeeder::class);
        $timeline = Timeline::has('posts','>=',1)->first();
        $creator = $timeline->user;

        $response = $this->actingAs($creator)->ajaxJSON('GET', route('comments.index'), [
            'user_id' => $creator->id,
        ]);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->comments);
        $commentsR = collect($content->comments);
        $this->assertGreaterThan(0, $commentsR->count());
        $this->assertObjectHasAttribute('description', $commentsR[0]);
        $commentsR->each( function($c) use(&$creator) { // all belong to owner
            $this->assertEquals($creator->id, $c->user_id);
        });
    }

    /**
     *  @group devcomment
     */
    public function test_can_not_index_general_comments()
    {
        // should return only comments for which session user is the author

        //$this->seed(\Database\Seeders\TestDatabaseSeeder::class);
        $timeline = Timeline::has('posts','>=',1)->first();
        $creator = $timeline->user;
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('comments.index'));
        $response->assertStatus(403);
    }

    /**
     *  @group devcomment
     */
    public function test_can_show_my_comment()
    {
        $timeline = Timeline::has('posts','>=',1)->first();
        $creator = $timeline->user;
        $comment = Comment::where('user_id', $creator->id)->first();

        $response = $this->actingAs($creator)->ajaxJSON('GET', route('comments.show', $comment->id));
        $response->assertStatus(200);
    }

    /**
     *  @group OFF-devcomment
     */
    public function test_can_show_followed_timelines_comment()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first();
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
     *  @group OFF-devcomment
     */
    public function test_can_store_comment()
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
     *  @group OFF-devcomment
     */
    public function test_can_update_comment()
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
     *  @group OFF-devcomment
     */
    public function test_can_destroy_comment()
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
     *  @group OFF-devcomment
     */
    // %TODO: unlike
    public function test_can_like_comment()
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
     *  @group OFF-devcomment
     */
    public function test_can_comment_on_comment()
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
        $response->assertStatus(200);
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
        $this->assertEquals(1, $commentsByFan->count());
        $this->assertEquals($payload['description'], $commentsByFan[0]->description);
        $this->assertEquals($commentsByFan[0]->id, $commentR->id);
    }

    /**
     *  @group TODO-devcomment
     */
    public function test_can_share_comment()
    {
    }

    /**
     *  @group TODO-devcomment
     */
    public function test_can_tip_comment()
    {
    }

    /**
     *  @group TODO-devcomment
     */
    public function test_can_purchase_comment()
    {
    }

    /**
     *  @group TODO-devcomment
     */
    public function test_can_pin_comment()
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

