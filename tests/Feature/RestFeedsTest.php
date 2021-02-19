<?php
namespace Tests\Feature;

use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

use App\Enums\PaymentTypeEnum;
use App\Enums\PostTypeEnum;
use App\Models\Post;
use App\Models\Timeline;
use App\Models\User;

class RestFeedsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group feeds
     *  @group regression
     *  @group here
     */
    public function test_view_home_feed()
    {
        $fan = User::has('followedtimelines', '>=', 2)->firstOrFail();

        // make sure at least one is default and one is premium, note this will mess up ledger but that's ok for this test
        $ft0 = $fan->followedtimelines[0];
        $ft1 = $fan->followedtimelines[1];
        $fan->followedtimelines()->updateExistingPivot($ft0->id, [
            'access_level' => 'default',
        ]);
        $fan->followedtimelines()->updateExistingPivot($ft1->id, [
            'access_level' => 'premium',
        ]);
        $fan->refresh();

        $this->assertGreaterThan(0, $fan->followedtimelines()->where('access_level', 'default')->count());
        $this->assertGreaterThan(0, $fan->subscribedtimelines->count());

        $payload = [];
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('feeds.home'), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertObjectHasAttribute('feeditems', $content);
        $fan->refresh();

        $posts = collect($content->feeditems);

        $followedTimelines = $fan->followedtimelines()->pluck('id');
        $expected = Post::where('postable_type', 'timelines')
            ->join('timelines', 'timelines.id', '=', 'posts.postable_id')
            ->whereIn('timelines.id', $followedTimelines)
            ->whereIn('type', [PostTypeEnum::FREE, PostTypeEnum::PRICED, PostTypeEnum::SUBSCRIBER])
            ->count();
        $this->assertGreaterThan(0, count($posts));
        $this->assertEquals($expected, count($posts));

        // check no posts from timelines I don't follow
        $num = $posts->reduce( function($acc, $p) use(&$fan) {
            $post = Post::find($p->id);
            $this->assertNotNull($post);
            return ($post->timeline->followers->contains($fan->id)) ? $acc : ($acc+1);
        }, 0);
        $this->assertEquals(0, $num, 'Found post in feed from non-followed timeline');

        // check has posts from every timeline I follow that has posts
        $num = $fan->followedtimelines->reduce( function($acc, $t) use(&$fan, &$posts) {
            if ( $t->posts->count() && !$posts->contains('id', $t->posts[0]->id) ) { // %TODO %CHECKME
                $acc += 1;
            }
            return $acc;
        },0 );
        $this->assertEquals(0, $num, 'Found followed timeline with posts not present in feed');
    }

    /**
     *  @group feeds
     *  @group regression
     *  @group here
     */
    // view a free feed I'm following
    public function test_view_as_follower_of_free_feed()
    {
        $timeline = Timeline::where('is_follow_for_free', true)->has('posts','>=',1)->has('followers','>=',1)->firstOrFail();
        $creator = $timeline->user;
        $fan = $timeline->followers()->whereDoesntHave('subscribedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        $payload = [];
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('feeds.show', $timeline->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertObjectHasAttribute('feeditems', $content);

        // %NOTE: this calc of expected assumes no free posts can be shared (thus aren't double-counted)
        $expectedNumFree = DB::table('posts')->where('postable_type', 'timelines')
                                             ->where('postable_id', $timeline->id)
                                             ->where('type', PostTypeEnum::FREE)
                                             ->count();
        $expectedNumPurchased = DB::table('shareables')
            ->join('posts', 'posts.id', '=', 'shareables.shareable_id')
            ->join('timelines', 'timelines.id', '=', 'posts.postable_id')
            ->where('shareables.sharee_id', $fan->id)
            ->where('shareables.shareable_type', 'posts')
            ->where('posts.postable_type', 'timelines')
            ->where('timelines.id', $timeline->id)
            ->count();
        $expected = $expectedNumPurchased + $expectedNumFree;
        $this->assertEquals($expected, count($content->feeditems));

        // check that we didn't miss any free posts
        $this->assertEquals($expectedNumFree, collect($content->feeditems)->reduce( function($acc, $p) {
            return $acc + ( ($p->type===PostTypeEnum::FREE) ? 1 : 0 );
        }, 0));

        // check that we don't have any purchase-only posts that don't belong to the follower
        $priced = collect($content->feeditems)->filter( function($p) use(&$fan) {
            $post = Post::find($p->id);
            $this->assertNotNull($post);
            return $post->type === PostTypeEnum::PRICED
                && !$post->sharees->contains($fan->id);
        });
        $this->assertEquals(0, $priced->count(), 'Should not include any non-purchased priced posts');

        // check that we don't have any subcriber-only posts
        $subscriber = collect($content->feeditems)->filter( function($p) {
            return $p->type === PostTypeEnum::SUBSCRIBER;
        });
        $this->assertEquals(0, $subscriber->count(), 'Should not include any subscriber posts');

    }

    /**
     *  @group feeds
     *  @group regression
     */
    // view a paid feed I'm following
    public function test_view_as_follower_of_paid_feed()
    {
        $timeline = Timeline::where('is_follow_for_free', false)->has('posts','>=',1)->has('followers','>=',1)->firstOrFail();
        $creator = $timeline->user;
        $fan = $timeline->followers()->whereDoesntHave('subscribedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        $payload = [];
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('feeds.show', $timeline->id), $payload);
        $response->assertStatus(200);
    }

    /**
     *  @group feeds
     *  @group regression
     */
    // view a paid feed I'm subscribed to
    public function test_view_as_subscriber_of_paid_feed()
    {
        $timeline = Timeline::has('posts','>=',1)->has('subscribers','>=',1)->firstOrFail();
        $creator = $timeline->user;
        $fan = $timeline->subscribers()->where('id', '<>', $creator->id)->first();
        $this->assertNotNull($fan);

        $payload = [];
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('feeds.show', $timeline->id), $payload);
        $response->assertStatus(200);
    }


    /**
     *  @group feeds
     *  @group regression
     */
    public function test_follower_can_view_followed_feed_free_posts_only()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->firstOrFail();
        $creator = $timeline->user;
        $fan = $timeline->followers()->whereDoesntHave('subscribedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        $payload = [];
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('feeds.show', $timeline->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());

        $expected = Post::where('postable_type', 'timelines')
            ->where('postable_id', $timeline->id)
            ->whereIn('type', [PostTypeEnum::FREE])
            //->whereIn('type', [PostTypeEnum::FREE, PostTypeEnum::PRICED])
            ->count();
        $this->assertEquals($expected, count($content->feeditems));


        // upgrade to subscriber...
        /*
        $this->assertNotNull($content->feeditems);
        $this->assertObjectHasAttribute('current_page', $content->feeditems);
        $this->assertObjectHasAttribute('data', $content->feeditems);
        $this->assertGreaterThan(0, count($content->feeditems->data));
        $this->assertEquals(1, $content->feeditems->current_page);
         */

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

