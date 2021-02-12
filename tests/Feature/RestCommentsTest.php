<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use DB;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Timeline;
use App\Models\User;
use App\Enums\PostTypeEnum;

class RestCommentsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group comments
     *  @group regression
     */
    // %TODO: filters, timelines (see comments I have valid access to), etc
    public function test_can_index_comments()
    {
        // should return only comments for which session user is the author

        $post = Post::has('comments','>=',1)->first();
        $creator = $post->timeline->user;

        // ensure post has a comment by creator
        $comment = Comment::create([
            //'post_id' => $post->id,
            'commentable_id'     => $post->id,
            'commentable_type'   => 'posts',
            'user_id' => $creator->id,
            'description' => $this->faker->realText,
        ]);

        $response = $this->actingAs($creator)->ajaxJSON('GET', route('comments.index'), [
            'user_id' => $creator->id,
        ]);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->comments);
        $commentsR = collect($content->comments);
        $this->assertGreaterThan(0, $commentsR->count());
        $this->assertObjectHasAttribute('description', $commentsR->first());
        $commentsR->each( function($c) use(&$creator) { // all belong to owner
            $this->assertEquals($creator->id, $c->user_id);
        });
    }

    /**
     *  @group comments
     *  @group regression
     */
    public function test_can_not_index_general_comments()
    {
        // should return only comments for which session user is the author

        $timeline = Timeline::has('posts','>=',1)->first();
        $creator = $timeline->user;
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('comments.index'));
        $response->assertStatus(403);
    }

    /**
     *  @group comments
     *  @group regression
     */
    public function test_can_show_my_comment()
    {
        $creator = User::has('comments', '>', 1)->first();
        $comment = Comment::where('user_id', $creator->id)->first();
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('comments.show', $comment->id));
        $response->assertStatus(200);
    }

    /**
     *  @group comments
     *  @group regression
     */
    public function test_can_show_followed_timelines_comment()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->has('comments', '>=', 1)->where('type', PostTypeEnum::FREE);
            })->first();
        $creator = $timeline->user;
        $fan = $timeline->followers->first();

        $post = $timeline->posts()->has('comments','>=',1)->first();
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);

        $comment = $post->comments->first();
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('comments.show', $comment->id));
        $response->assertStatus(200);
    }

    /**
     *  @group comments
     *  @group regression
     */
    public function test_can_not_show_non_followed_timelines_comment()
    {
        $post = Post::has('comments','>=',1)->first();
        $timeline = $post->timeline;
        $creator = $timeline->user;
        $fan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();
        $this->assertFalse( $timeline->followers->contains( $fan->id ) );

        $post = $timeline->posts()->has('comments','>=',1)->first();
        $comment = $post->comments->first();
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('comments.show', $comment->id));
        $response->assertStatus(403);
    }

    /**
     *  @group comments
     *  @group regression
     */
    public function test_timeline_follower_can_store_comment_on_post()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->first();
        $post = $timeline->posts->first();
        $creator = $timeline->user;
        $fan = $timeline->followers->first();
//dump('test', $fan->id, $creator->id);

        // remove any existing comments on post by fan...
        DB::table('comments')
            ->where('commentable_id', $post->id)
            ->where('commentable_type', 'posts')
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
        $this->assertEquals($payload['description'], $commentsByFan->first()->description);
        $this->assertEquals($commentsByFan->first()->id, $commentR->id);
    }

    public function test_can_store_comment_on_followed_timeline()
    {
        $response->assertStatus(201);
    }

    /**
     *  @group comments
     *  @group regression
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
     *  @group comments
     *  @group regression
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
     *  @group comments
     *  @group regression
     */
    public function test_timeline_follower_can_like_then_unlike_post_comment()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->has('comments', '>=', 1)->where('type', PostTypeEnum::FREE);
            })->first();
        $creator = $timeline->user;
        $fan = $timeline->followers->first();
        $post = $timeline->posts()->has('comments','>=',1)->first(); // %FIXME: sometimes this will not exist
        $comment = $post->comments->first();
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

