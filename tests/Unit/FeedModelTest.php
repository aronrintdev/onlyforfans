<?php
namespace Tests\Unit;

use Tests\TestCase;

use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use DB;
use Exception;
use Database\Seeders\TestDatabaseSeeder;

use App\Models\Feed;
use App\Enums\PaymentTypeEnum;
use App\Enums\PostTypeEnum;
use App\Models\Post;
use App\Models\Timeline;
use App\Models\User;

class FeedModelTest extends TestCase
{
    use WithFaker;

    protected $timeline;
    protected $creator;
    protected $follower;
    protected $subscriber;

    /**
     * @group feed-model
     * @group todo-deprecate
     */
    public function test_feed_get_owner_feed()
    {
        $posts = Feed::getOwnerFeed($this->timeline);
        $expected = Post::where('postable_type', 'timelines')
            ->where('postable_id', $this->timeline->id)
            ->count();
        $this->assertEquals($expected, count($posts));
    }

    /**
     * @group feed-model
     * @group todo-deprecate
     */
    public function test_feed_get_public_feed()
    {
        $posts = Feed::getPublicFeed($this->timeline);
        $expected = Post::where('postable_type', 'timelines')
            ->where('postable_id', $this->timeline->id)
            ->whereIn('type', [PostTypeEnum::FREE])
            ->count();
        $this->assertEquals($expected, count($posts));
    }

    /**
     * @group feed-model
     * @group todo-deprecate
     */
    public function test_feed_get_home_feed()
    {
        // --- Setup ---
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

        // --- Execute ---

        $posts = Feed::getHomeFeed($fan);

        // --- Checks ---

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
            return ($p->timeline->followers->contains($fan->id)) ? $acc : ($acc+1);
        }, 0);
        $this->assertEquals(0, $num, 'Found post in feed from non-followed timeline');

        // check has posts from every timeline I follow that has posts
        $num = $fan->followedtimelines->reduce( function($acc, $t) use(&$fan, &$posts) {
            if ( $t->posts->count() && !$posts->contains($t->posts[0]->id) ) { // %TODO %CHECKME
                $acc += 1;
            }
            return $acc;
        },0 );
        $this->assertEquals(0, $num, 'Found followed timeline with posts not present in feed');
    }

    /**
     * @group feed-model
     * @group todo-deprecate
     */
    // get free + post-purchased-by-user + subscriber-only by timeline (eg: as fan viewing subscribed timeline)
    public function test_feed_get_subscribing_feed()
    {
        $posts = Feed::getSubscriberFeed($this->timeline, $this->subscriber);

        // %NOTE: this calc of expected assumes no free posts can be shared (thus aren't double-counted)
        $expectedNumFree = DB::table('posts')->where('postable_type', 'timelines')
                                             ->where('postable_id', $this->timeline->id)
                                             ->where('type', PostTypeEnum::FREE)
                                             ->count();
        $expectedNumPurchased = DB::table('shareables')
            ->join('posts', 'posts.id', '=', 'shareables.shareable_id')
            ->join('timelines', 'timelines.id', '=', 'posts.postable_id')
            ->where('shareables.sharee_id', $this->follower->id)
            ->where('shareables.shareable_type', 'posts')
            ->where('posts.postable_type', 'timelines')
            ->where('timelines.id', $this->timeline->id)
            ->count();
        $expectedNumSubscriber = DB::table('posts')->where('postable_type', 'timelines')
                                                   ->where('postable_id', $this->timeline->id)
                                                   ->where('type', PostTypeEnum::SUBSCRIBER)
                                                   ->count();

        $expected = $expectedNumPurchased + $expectedNumFree + $expectedNumSubscriber;
        $this->assertEquals($expected, count($posts));

        // check that we didn't miss any free posts
        $this->assertEquals($expectedNumFree, $posts->reduce( function($acc, $p) {
            return $acc + ( ($p->type===PostTypeEnum::FREE) ? 1 : 0 );
        }, 0));

        // check that we don't have any purchase-only posts that don't belong to the subscriber
        $priced = $posts->filter( function($p) {
            return $p->type === PostTypeEnum::PRICED
                && !$p->sharees->contains($this->subscriber->id);
        });
        $this->assertEquals(0, $priced->count(), 'Should not include any non-purchased priced posts');

        // check that we didn't miss any subscriber-only posts
        $this->assertEquals($expectedNumSubscriber, $posts->reduce( function($acc, $p) {
            return $acc + ( ($p->type===PostTypeEnum::SUBSCRIBER) ? 1 : 0 );
        }, 0));
    }

    /**
     * @group feed-model
     * @group todo-deprecate
     */
    // get free + post-purchased-by-user by timeline (eg: as fan viewing followed timeline)
    public function test_feed_get_following_feed()
    {
        $posts = Feed::getFollowerFeed($this->timeline, $this->follower);

        // %NOTE: this calc of expected assumes no free posts can be shared (thus aren't double-counted)
        $expectedNumFree = DB::table('posts')->where('postable_type', 'timelines')
                                             ->where('postable_id', $this->timeline->id)
                                             ->where('type', PostTypeEnum::FREE)
                                             ->count();
        $expectedNumPurchased = DB::table('shareables')
            ->join('posts', 'posts.id', '=', 'shareables.shareable_id')
            ->join('timelines', 'timelines.id', '=', 'posts.postable_id')
            ->where('shareables.sharee_id', $this->follower->id)
            ->where('shareables.shareable_type', 'posts')
            ->where('posts.postable_type', 'timelines')
            ->where('timelines.id', $this->timeline->id)
            ->count();
        $expected = $expectedNumPurchased + $expectedNumFree;
        $this->assertEquals($expected, count($posts));

        // check that we didn't miss any free posts
        $this->assertEquals($expectedNumFree, $posts->reduce( function($acc, $p) {
            return $acc + ( ($p->type===PostTypeEnum::FREE) ? 1 : 0 );
        }, 0));

        // check that we don't have any purchase-only posts that don't belong to the follower
        $priced = $posts->filter( function($p) {
            return $p->type === PostTypeEnum::PRICED
                && !$p->sharees->contains($this->follower->id);
        });
        $this->assertEquals(0, $priced->count(), 'Should not include any non-purchased priced posts');

        // check that we don't have any subcriber-only posts
        $subscriber = $posts->filter( function($p) {
            return $p->type === PostTypeEnum::SUBSCRIBER;
        });
        $this->assertEquals(0, $subscriber->count(), 'Should not include any subscriber posts');
    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
        //$this->seed(TestDatabaseSeeder::class);

        $timeline = Timeline::has('posts','>=',1)
            ->has('subscribers','>=',1)
            ->has('followers','>=',1)
            ->firstOrFail();
        $creator = $timeline->user;

        $follower = $timeline->followers()->whereDoesntHave('subscribedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->firstOrFail();

        $subscriber = $timeline->subscribers()->where('id','<>',$creator->id)->first();
        $this->assertNotNull($subscriber);

        $this->timeline = $timeline;
        $this->creator = $creator;
        $this->follower = $follower;
        $this->subscriber = $subscriber;

        $this->assertFalse($subscriber->id === $follower->id);
        $this->assertFalse($creator->id === $follower->id);
        $this->assertTrue($timeline->followers->contains($follower->id));
        $this->assertFalse($timeline->subscribers->contains($follower->id));
        $this->assertTrue($timeline->subscribers->contains($subscriber->id));
    }

    protected function tearDown() : void {
        parent::tearDown();
    }

}
