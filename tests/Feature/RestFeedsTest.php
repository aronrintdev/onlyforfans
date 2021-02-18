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
        dd(
            count($content->feeditems),
            $timeline->posts->count(),
        );
         */
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

