<?php
namespace Tests\Unit;

use Tests\TestCase;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    use RefreshDatabase, WithFaker;

    protected $timeline;
    protected $creator;
    protected $follower;
    protected $subscriber;

    /**
     * @group feed-model
     * @group regression
     */
    public function test_feed_get_by_timeline_for_follower_strict()
    {
        $posts = Feed::getByTimeline($this->timeline, $this->follower, true);
        $posts->load('sharees');

        // %NOTE: this calc of expected assumes no free posts can be shared (thus aren't double-counted)
        $expectedNumFree = DB::table('posts')->where('postable_type', 'timelines')
                                             ->where('postable_id', $this->timeline->id)
                                             ->where('type', PostTypeEnum::FREE)
                                             ->count();
        $expectedNumPriced = DB::table('shareables')
            ->join('posts', 'posts.id', '=', 'shareables.shareable_id')
            ->join('timelines', 'timelines.id', '=', 'posts.postable_id')
            ->where('shareables.sharee_id', $this->follower->id)
            ->where('shareables.shareable_type', 'posts')
            ->where('posts.postable_type', 'timelines')
            ->where('timelines.id', $this->timeline->id)
            ->count();
        $expected = $expectedNumPriced + $expectedNumFree;
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

    /**
     * @group feed-model
     * @group regression
     * @group here
     */
    public function test_feed_get_by_timeline_for_subscriber_strict()
    {
        $posts = Feed::getByTimeline($this->timeline, $this->subscriber, true);
        $posts->load('sharees');

        // %NOTE: this calc of expected assumes no free posts can be shared (thus aren't double-counted)
        $expectedNumFree = DB::table('posts')->where('postable_type', 'timelines')
                                             ->where('postable_id', $this->timeline->id)
                                             ->where('type', PostTypeEnum::FREE)
                                             ->count();
        $expectedNumPriced = DB::table('shareables')
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

        $expected = $expectedNumPriced + $expectedNumFree + $expectedNumSubscriber;
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
     * @group OFF-regression
     */
    // Will get all posts, even ones the follower hasn't purchased, etc...
    //   ~ if a post is not accessible, will be shown as 'locked' in UI with a CTA
    public function test_feed_get_by_timeline()
    {
        $posts = Feed::getByTimeline($this->timeline, $this->follower, false);

        $expected = Post::where('postable_type', 'timelines')
            ->where('postable_id', $this->timeline->id)
            ->whereIn('type', [PostTypeEnum::FREE, PostTypeEnum::PRICED, PostTypeEnum::SUBSCRIBER])
            ->count();
        $this->assertEquals($expected, count($posts));
    }

    /**
     * @group feed-model
     * @group OFF-regression
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
     * @group OFF-regression
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

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
        $this->seed(TestDatabaseSeeder::class);

        $timeline = Timeline::has('posts','>=',1)
            ->has('subscribers','>=',1)
            ->has('followers','>=',1)
            ->firstOrFail();
        $creator = $timeline->user;

        try {
            $follower = $timeline->followers()->whereDoesntHave('subscribedtimelines', function($q1) use(&$timeline) {
                $q1->where('timelines.id', $timeline->id);
            })->where('id', '<>', $creator->id)->firstOrFail();
        } catch (Exception $e) {
            dd('not found', 
                $timeline->followers->toArray()
            );
        }

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
