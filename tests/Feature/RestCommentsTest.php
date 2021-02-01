<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
     *  @group group_comments
     *  @group group_regression
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
     *  @group group_comments
     *  @group group_regression
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
     *  @group group_comments
     *  @group group_regression
     */
    public function test_can_show_my_comment()
    {
        //$timeline = Timeline::has('posts','>=',1)->first();
        //$creator = $timeline->user;
        $creator = User::has('comments', '>', 1)->first();
        $comment = Comment::where('user_id', $creator->id)->first();
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('comments.show', $comment->id));
        $response->assertStatus(200);
    }

    /**
     *  @group group_comments
     *  @group group_regression
     */
    public function test_can_show_followed_timelines_comment()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first();
        $creator = $timeline->user;
        $post = $timeline->posts[0];
        $fan = $timeline->followers[0];
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('comments.show', $post->id));
        $response->assertStatus(200);
    }

    /**
     *  @group group_comments
     *  @group group_regression
     */
    public function test_can_not_show_nonfollowed_timelines_comment()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers',0)->first();
        $this->assertNotNull($timeline);
        $this->assertEquals(0, $timeline->followers->count());
        $post = $timeline->posts[0];
        $creator = $timeline->user;
        $fan = User::where('id', '<>', $creator->id)->first(); // some user other than owner of timeline
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('comments.show', $post->comments->first()->id));
        $response->assertStatus(403);
    }

    /**
     *  @group group_comments
     *  @group group_regression
     */
    public function test_timeline_follower_can_store_comment_on_post()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers',1)->first();
        $post = $timeline->posts[0];
        $creator = $timeline->user;
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
        $response = $this->actingAs($fan)->ajaxJSON('POST', route('comments.store'), $payload);
        $response->assertStatus(201);
        $post->refresh();
        $post->load('comments');

        $content = json_decode($response->content());
        $this->assertObjectHasAttribute('comment', $content);
        $commentR = $content->comment;

        $this->assertNotNull($post->comments);
        $this->assertGreaterThan(0, $post->comments->count());
        $this->assertEquals($origCommentCount+1, $post->comments->count());

        $commentsByFan = $post->comments->filter( function($v,$k) use($fan) {
            return $v->user_id === $fan->id;
        });
        $this->assertEquals(1, $commentsByFan->count());
        $this->assertEquals($payload['description'], $commentsByFan[0]->description);
        $this->assertEquals($commentsByFan[0]->id, $commentR->id);
    }

    public function test_can_store_comment_on_followed_timeline()
    {
        $response->assertStatus(201);
    }

    /**
     *  @group group_comments
     *  @group group_regression
     */
    public function test_can_update_own_comment()
    {
        $creator = User::has('comments', '>', 1)->first();
        $comment = Comment::where('user_id', $creator->id)->first();
        $payload = [
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('comments.update', $comment->id), $payload);
        $response->assertStatus(200);
    }

    /**
     *  @group group_comments
     *  @group group_regression
     */
    public function test_can_destroy_own_comment()
    {
        $creator = User::has('comments', '>', 1)->first();
        $comment = Comment::where('user_id', $creator->id)->first();
        $response = $this->actingAs($creator)->ajaxJSON('DELETE', route('comments.destroy', $comment->id));
        $response->assertStatus(200);
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('comments.show', $comment->id));
        $response->assertStatus(404);
    }

    /**
     *  @group group_comments
     *  @group group_regression
     */
    public function test_timeline_follower_can_like_then_unlike_post_comment()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); 
        $creator = $timeline->user;
        $fan = $timeline->followers[0];
        $post = $timeline->posts()->has('comments','>=',1)->first(); // %FIXME: sometimes this will not exist
        $comment = $post->comments[0];
        $this->assertNotNull($comment);

        // remove any existing likes by fan...
        $likeable = DB::table('likeables')
            ->where('likee_id', $fan->id)
            ->where('likeable_type', 'comments')
            ->where('likeable_id', $comment->id)
            ->first();
        if ($likeable) {
            $likeable->delete();
        }
        unset($likeable);

        // LIKE the comment
        $payload = [
            'likeable_type' => 'comments',
            'likeable_id' => $comment->id,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('likeables.update', $fan->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->likeable);
        $commentR = $content->likeable;
        $this->assertEquals($comment->id, $commentR->id);

        $likeable = DB::table('likeables')
            ->where('likee_id', $fan->id)
            ->where('likeable_type', 'comments')
            ->where('likeable_id', $commentR->id)
            ->first();
        $this->assertNotNull($likeable);
        $this->assertEquals($fan->id, $likeable->likee_id);
        $this->assertEquals('comments', $likeable->likeable_type);
        $this->assertEquals($commentR->id, $likeable->likeable_id);

        // UNLIKE the comment
        $payload = [
            'likeable_type' => 'comments',
            'likeable_id' => $comment->id,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('DELETE', route('likeables.destroy', $fan->id), $payload); // fan->likee
        $response->assertStatus(200);

        $likeable = DB::table('likeables')
            ->where('likee_id', $fan->id)
            ->where('likeable_type', 'comments')
            ->where('likeable_id', $commentR->id)
            ->first();
        $this->assertNull($likeable);
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

