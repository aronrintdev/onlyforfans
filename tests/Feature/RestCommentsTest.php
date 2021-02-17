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
    // should return only comments for which session user is the author
    // %TODO: filters, timelines (see comments I have valid access to), etc
    public function test_can_index_comments()
    {
        $post = Post::has('comments','>=',1)->first();
        $creator = $post->timeline->user;
        $expectedCount = Comment::where('user_id', $creator->id)->count();

        $response = $this->actingAs($creator)->ajaxJSON('GET', route('comments.index'), [
            'user_id' => $creator->id,
        ]);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->comments);
        $this->assertGreaterThan(0, count($content->comments));
        $this->assertEquals($expectedCount, count($content->comments));
        $this->assertObjectHasAttribute('description', $content->comments[0]);
        collect($content->comments)->each( function($c) use(&$creator) { // all belong to owner
            $this->assertEquals($creator->id, $c->user_id);
        });
    }

    /**
     *  @group comments
     *  @group regression
     */
    public function test_nonowner_can_not_index_comments()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->has('comments', '>=', 1)->where('type', PostTypeEnum::FREE);
            })->firstOrFail();
        $creator = $timeline->user;
        $fan = $timeline->followers[0]; // test as a follower
        $post = $timeline->posts()->where('type', PostTypeEnum::FREE)->has('comments','>=',1)->first();
        $expectedCount = Comment::where('user_id', $creator->id)->count();

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('comments.index'), [
            'user_id' => $creator->id,
        ]);
        $response->assertStatus(403);
    }

    /**
     *  @group comments
     *  @group regression
     */
     // should return only comments for which session user is the author
    public function test_nonadmin_can_not_index_general_comments()
    {
        $timeline = Timeline::has('posts','>=',1)->first();
        $creator = $timeline->user;
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('comments.index'));
        $response->assertStatus(403);
    }

    /**
     *  @group comments
     *  @group regression
     */
    public function test_owner_can_view_own_comment()
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
    public function test_follower_can_view_timeline_comments()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->has('comments', '>=', 1)->where('type', PostTypeEnum::FREE);
            })->firstOrFail();
        $creator = $timeline->user;
        $fan = $timeline->followers[0];
        $post = $timeline->posts()->where('type', PostTypeEnum::FREE)->has('comments','>=',1)->first();

        // can view individual post
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);

        // can index post's comments
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.indexComments', $post->id));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->comments);
        $this->assertGreaterThan(0, count($content->comments));

        // can view individual comment
        $comment = $post->comments[0];
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('comments.show', $comment->id));
        $response->assertStatus(200);
    }

    /**
     *  @group comments
     *  @group regression
     */
    public function test_can_not_view_non_followed_timelines_comment()
    {
        $post = Post::has('comments','>=',1)->first();
        $timeline = $post->timeline;
        $creator = $timeline->user;
        $nonfan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();
        $this->assertFalse( $timeline->followers->contains( $nonfan->id ) );

        $post = $timeline->posts()->has('comments','>=',1)->first();
        $comment = $post->comments[0];
        $response = $this->actingAs($nonfan)->ajaxJSON('GET', route('comments.show', $comment->id));
        $response->assertStatus(403);
    }

    /**
     *  @group comments
     *  @group regression
     *  @group here
     *  @group erik
     */
    public function test_timeline_follower_can_create_comment_on_post()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->first();
        $creator = $timeline->user;
        $fan = $timeline->followers[0];
        $post = $timeline->posts[0];

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

    /**
     *  @group comments
     *  @group regression
     */
    public function test_can_edit_own_comment()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->first();
        $owner = $timeline->user;
        $post = $timeline->posts()->has('comments', '>=', 1)->first();
        $comment = $post->comments()->where('user_id', '<>', $owner->id)->first();
        $fan = $comment->user;

        $payload = [
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PATCH', route('comments.update', $comment->id), $payload);
        $response->assertStatus(200);
    }

    /*
     *  @group comments
     *  @group regression
     */
    public function test_can_not_edit_nonowned_comment()
    {
        $owner = User::has('comments', '>', 1)->first();
        $comment = Comment::where('user_id', $owner->id)->first();
        $nonowner = User::where('id', '<>', $owner->id)->first();

        $payload = [
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($nonowner)->ajaxJSON('PATCH', route('comments.update', $comment->id), $payload);
        $response->assertStatus(403);
    }

    /**
     *  @group comments
     *  @group regression
     */
    public function test_can_delete_own_comment()
    {
        $owner = User::has('comments', '>', 1)->first();
        $comment = Comment::where('user_id', $owner->id)->first();
        $response = $this->actingAs($owner)->ajaxJSON('DELETE', route('comments.destroy', $comment->id));
        $response->assertStatus(200);
        $response = $this->actingAs($owner)->ajaxJSON('GET', route('comments.show', $comment->id));
        $response->assertStatus(404);
    }

    /*
     *  @group comments
     *  @group regression
     */
    public function test_can_not_delete_nonowned_comment()
    {
        $owner = User::has('comments', '>', 1)->first();
        $comment = Comment::where('user_id', $owner->id)->first();
        $nonowner = User::where('id', '<>', $owner->id)->first();
        $response = $this->actingAs($nonowner)->ajaxJSON('DELETE', route('comments.destroy', $comment->id));
        $response->assertStatus(403);
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

