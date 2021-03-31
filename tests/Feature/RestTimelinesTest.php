<?php
namespace Tests\Feature;

use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

use App\Enums\MediafileTypeEnum;
use App\Enums\PostTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Models\Fanledger;
use App\Models\Post;
use App\Models\Mediafile;
use App\Models\Timeline;
use App\Models\User;

class TimelinesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_owner_can_view_home_feed()
    {
        //$timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)

        // get timeline that has both priced & subscriber posts
        $timeline = Timeline::whereHas('posts', function($q1) {
            $q1->whereIn('type', [PostTypeEnum::SUBSCRIBER, PostTypeEnum::PRICED]); // ?? this is OR not AND 
        })->has('followers','>=',1)->firstOrFail();
        $creator = $timeline->user;

        $payload = [];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('timelines.homefeed'), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        //dd($content);
        $posts = collect($content->data);

        $num = $posts->reduce( function($acc, $p) {
            return ( $p->type!==PostTypeEnum::FREE) ? ($acc+1) : $acc;
        }, 0);
        $this->assertGreaterThan(0, $num, 'Feed should contain at least 1 non-free post');
    }

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_owner_can_sort_home_feed_by_like_count()
    {
        $timeline = Timeline::whereHas('posts', function($q1) {
            $q1->whereIn('type', [PostTypeEnum::SUBSCRIBER, PostTypeEnum::PRICED]); // ?? this is OR not AND 
        })->has('followers','>=',1)->firstOrFail();
        $creator = $timeline->user;

        $payload = ['sortBy'=>'likes'];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('timelines.homefeed'), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $posts = collect($content->data);

        $num = $posts->reduce( function($acc, $p) {
            static $last = null;
            if ( $last && ($p->stats->likeCount > $last->stats->likeCount) ) {
                $acc += 1;
            }
            $last = $p;
            return $acc;
        }, 0);
        $this->assertEquals(0, $num, 'Feed should not contain any out-of-order (non-sorted) posts by like count');
    }

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_owner_can_sort_home_feed_by_comment_count()
    {
        $timeline = Timeline::whereHas('posts', function($q1) {
            $q1->whereIn('type', [PostTypeEnum::SUBSCRIBER, PostTypeEnum::PRICED]); // ?? this is OR not AND 
        })->has('followers','>=',1)->firstOrFail();
        $creator = $timeline->user;

        $payload = ['sortBy'=>'comments'];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('timelines.homefeed'), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $posts = collect($content->data);

        $num = $posts->reduce( function($acc, $p) {
            static $last = null;
            if ( $last && ($p->stats->commentCount > $last->stats->commentCount) ) {
                $acc += 1;
            }
            $last = $p;
            return $acc;
        }, 0);
        $this->assertEquals(0, $num, 'Feed should not contain any out-of-order (non-sorted) posts by comment count');
    }

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_owner_can_filter_home_feed_by_nonlocked()
    {
        // %FIXME: we aren't really guaranteed a timeine with non-accessible posts...
        $timeline = Timeline::whereHas('posts', function($q1) {
            $q1->whereIn('type', [PostTypeEnum::SUBSCRIBER, PostTypeEnum::PRICED]); // ?? this is OR not AND 
        })->has('followers','>=',1)->firstOrFail();
        $creator = $timeline->user;

        //$payload = [];
        $payload = ['hideLocked'=>'true'];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('timelines.homefeed'), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $posts = collect($content->data);

        $num = $posts->reduce( function($acc, $p) {
            return !$p->access ? ($acc+1) : $acc;
        }, 0);
        $this->assertEquals(0, $num, 'Feed should not contain any non-accessible posts');
    }

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_owner_can_view_own_timeline_feed()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $creator = $timeline->user;

        $payload = [];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('timelines.feed', $timeline->id), $payload);
        $response->assertStatus(200);

        //$content = json_decode($response->content());
        //$this->assertEquals(1, $content->meta->current_page);
        //$this->assertNotNull($content->data);
        //$this->assertGreaterThan(0, count($content->data));
    }

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_fan_can_not_access_locked_content_via_feed()
    {
        $timeline = Timeline::has('posts','>=',5)->has('followers','>=',1)->firstOrFail(); // assume non-admin (%FIXME)

        // Makes sure we have at least 1 free, 1 priced, and 1 subscibe-only post, then add some mediafiles to the posts...
        $posts = Post::where('postable_type', 'timelines')->where('postable_id', $timeline->id)->latest()->take(5)->get();

        $freePost = $posts[0];
        $freePost->type = PostTypeEnum::FREE;
        $freePost->save();
        $this->attachMediafile($freePost);
        $this->attachMediafile($freePost);

        $pricedPost = $posts[1];
        $pricedPost->type = PostTypeEnum::PRICED;
        $pricedPost->price = 3*100;
        $pricedPost->save();
        $this->attachMediafile($pricedPost);
        $this->attachMediafile($pricedPost);

        $subPost = $posts[2];
        $subPost->type = PostTypeEnum::SUBSCRIBER;
        $subPost->save();
        $this->attachMediafile($subPost);
        $this->attachMediafile($subPost);

        //$posts = Post::with('mediafiles')->where('postable_type', 'timelines')->where('postable_id', $timeline->id)->latest()->take(5)->get();
        //dd($posts);

        $timeline = Timeline::has('posts','>=',1)->has('posts.mediafiles')->has('followers','>=',1)->firstOrFail(); // assume non-admin (%FIXME)
        $creator = $timeline->user;
        $nonfan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->firstOrFail();
        $this->assertFalse( $timeline->followers->contains( $nonfan->id ) );
        $this->assertFalse( $nonfan->followedtimelines->contains( $timeline->id ) );

        // Follow the timeline (default, not premium)
        $response = $this->actingAs($nonfan)->ajaxJSON('PUT', route('timelines.follow', $timeline->id), ['sharee_id'=>$nonfan->id]);
        $response->assertStatus(200);
        $fan = $nonfan;
        $fan->refresh();
        $timeline->refresh();
        $this->assertTrue( $timeline->followers->contains( $fan->id ) );
        $this->assertTrue( $fan->followedtimelines->contains( $timeline->id ) );

        // --

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('timelines.feed', $timeline->id), []);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->data);
        $this->assertGreaterThan(0, count($content->data));
        //dd($content);
        unset($freePost, $pricedPost, $subPost);

        $posts = collect($content->data);

        $freePost = $posts->first( function($p) {
            return $p->type === PostTypeEnum::FREE;
        });
        $this->assertNotNull($freePost);
        $this->assertEquals(2, $freePost->mediafile_count);
        $this->assertTrue($freePost->access);
        $this->assertNotNull($freePost->mediafiles[0]);
        $this->assertNotNull($freePost->mediafiles[0]->filepath);

        $pricedPost = $posts->first( function($p) {
            return $p->type === PostTypeEnum::PRICED;
        });
        $this->assertNotNull($pricedPost);
        $this->assertEquals(2, $pricedPost->mediafile_count);
        $this->assertFalse($pricedPost->access);
        $this->assertNotNull($pricedPost->mediafiles[0]);
        $this->assertNull($pricedPost->mediafiles[0]->filepath); // can't access media!

        $subPost = $posts->first( function($p) {
            return $p->type === PostTypeEnum::SUBSCRIBER;
        });
        $this->assertNotNull($subPost);
        $this->assertEquals(2, $subPost->mediafile_count);
        $this->assertFalse($subPost->access);
        $this->assertNotNull($subPost->mediafiles[0]);
        $this->assertNull($subPost->mediafiles[0]->filepath); // can't access media!

        //dd($freePost, $pricedPost, $subPost);
    }

    /**
     *  @group timelines
     *  @group regression
     *  @group here0330
     */
    public function test_fan_can_view_photos_only_feed()
    {
        $timeline = Timeline::has('posts','>=',5)->has('followers','>=',1)->firstOrFail();
        $creator = $timeline->user;
        $fan = $timeline->followers[0];

        // Add some mediafiles (photos) to the posts...
        $posts = Post::where('postable_type', 'timelines')->where('postable_id', $timeline->id)->latest()->take(5)->get();
        $this->attachMediafile($posts[0]);
        $this->attachMediafile($posts[0]);
        $this->attachMediafile($posts[1]);
        $this->attachMediafile($posts[2]);
        $this->attachMediafile($posts[2]);
        $this->attachMediafile($posts[2]);
        $this->attachMediafile($posts[3]);

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('timelines.photos', $timeline->id), []);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [ 
                0 => [ 
                    'slug', 
                    'access', 
                    'basename', 
                    'mfname', 
                    'mftype', 
                    'filename', 
                    'resource_id', 
                    'resource_type', 
                    'has_thumb', 
                    'has_mid', 
                    'has_blur', 
                    'is_image', 
                    'is_video', 
                    'mimetype', 
                    'filepath', 
                    'thumbFilepath', 
                    'midFilepath', 
                    'blurFilepath', 
                ],
            ],
            'links',
            'meta',
        ]);
        $content = json_decode($response->content());
        $mediafiles = collect($content->data);
        //dd($content);
    }


    /**
     *  @group timelines
     *  @group regression
     */
    public function test_owner_can_view_own_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $creator = $timeline->user;

        $payload = [];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('timelines.show', $timeline->slug), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertObjectHasAttribute('slug', $content->data);
        $this->assertNotNull($content->data->slug);
        $this->assertObjectHasAttribute('name', $content->data);
        $this->assertNotNull($content->data->name);
        $this->assertObjectHasAttribute('about', $content->data);
        $this->assertNotNull($content->data->about);
        $this->assertObjectHasAttribute('cover', $content->data);
        $this->assertObjectHasAttribute('avatar', $content->data);

        $this->assertObjectNotHasAttribute('posts', $content->data);
        $this->assertObjectNotHasAttribute('user', $content->data);
        $this->assertObjectNotHasAttribute('followers', $content->data);
        $this->assertObjectNotHasAttribute('subscribers', $content->data);
        $this->assertObjectNotHasAttribute('ledgersales', $content->data);
        $this->assertObjectNotHasAttribute('stories', $content->data);
    }

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_owner_can_view_another_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $creator = $timeline->user;
        $nonfan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        $payload = [];
        $response = $this->actingAs($nonfan)->ajaxJSON('GET', route('timelines.show', $timeline->slug), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertObjectHasAttribute('slug', $content->data);
        $this->assertNotNull($content->data->slug);
        $this->assertObjectHasAttribute('name', $content->data);
        $this->assertNotNull($content->data->name);
        $this->assertObjectHasAttribute('about', $content->data);
        $this->assertNotNull($content->data->about);
        $this->assertObjectHasAttribute('cover', $content->data);
        $this->assertObjectHasAttribute('avatar', $content->data);
        $this->assertObjectNotHasAttribute('posts', $content->data);
        $this->assertObjectNotHasAttribute('user', $content->data);
        $this->assertObjectNotHasAttribute('followers', $content->data);
        $this->assertObjectNotHasAttribute('subscribers', $content->data);
        $this->assertObjectNotHasAttribute('ledgersales', $content->data);
        $this->assertObjectNotHasAttribute('stories', $content->data);
    }

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_can_view_suggested_timelines()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $creator = $timeline->user;
        $nonfan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        $payload = [];
        $response = $this->actingAs($nonfan)->ajaxJSON('GET', route('timelines.suggested'), $payload);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'timelines' => [0 => [ 'id', 'slug', 'name', 'about', 'verified', 'price', 'is_follow_for_free', 'cover', 'avatar', ] ]
        ]);
        //$content = json_decode($response->content());
    }

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_can_send_tip_to_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $creator = $timeline->user;
        $fan = $timeline->followers[0];

        $payload = [
            'base_unit_cost_in_cents' => $this->faker->randomNumber(3),
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.tip', $timeline->id), $payload);

        $response->assertStatus(200);

        $content = json_decode($response->content());
        $timelineR = $content->timeline;

        $fanledger = Fanledger::where('fltype', PaymentTypeEnum::TIP)
            ->where('purchaseable_type', 'timelines')
            ->where('purchaseable_id', $timeline->id)
            ->where('seller_id', $creator->id)
            ->where('purchaser_id', $fan->id)
            ->first();
        $this->assertNotNull($fanledger);
        $this->assertEquals(1, $fanledger->qty);
        $this->assertEquals($payload['base_unit_cost_in_cents'], $fanledger->base_unit_cost_in_cents);
        $this->assertTrue( $timeline->ledgersales->contains( $fanledger->id ) );
        $this->assertTrue( $fan->ledgerpurchases->contains( $fanledger->id ) );
    }

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_can_follow_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first();
        $creator = $timeline->user;

        // find a user who is not yet a follower (includes subscribers) of timeline
        $fan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();
        $this->assertFalse( $timeline->followers->contains( $fan->id ) );
        $this->assertFalse( $fan->followedtimelines->contains( $timeline->id ) );
        $origFollowerCount = $timeline->followers->count();

        $payload = [
            'sharee_id' => $fan->id,
            'notes'=>'test_can_follow_timeline',
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.follow', $timeline->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->timeline);
        $this->assertEquals($timeline->id, $content->timeline->id);
        $this->assertTrue( $content->is_following );
        $this->assertEquals( $origFollowerCount+1, $content->follower_count );

        $timeline->refresh();
        $fan->refresh();
        $this->assertEquals('default', $timeline->followers->find($fan->id)->pivot->access_level);
        $this->assertEquals('timelines', $timeline->followers->find($fan->id)->pivot->shareable_type);
        $this->assertTrue( $timeline->followers->contains( $fan->id ) );
        $this->assertTrue( $fan->followedtimelines->contains( $timeline->id ) );
    }

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_can_unfollow_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first();
        $creator = $timeline->user;
        $fan = $timeline->followers[0];
        $origFollowerCount = $timeline->followers->count();

        $payload = [
            'sharee_id' => $fan->id,
            'notes'=>'test_can_unfollow_timeline',
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.follow', $timeline->id), $payload); // toggles
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->timeline);
        $this->assertEquals($timeline->id, $content->timeline->id);
        $this->assertFalse( $content->is_following );
        $this->assertEquals( $origFollowerCount-1, $content->follower_count );

        $timeline->refresh();
        $this->assertFalse( $timeline->followers->contains( $fan->id ) );
        $this->assertFalse( $fan->followedtimelines->contains( $content->timeline->id ) );
    }

    /**
     *  @group timelines
     *  @group OFF-regression
     *  @group broken
     */
    public function test_blocked_can_not_follow_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first();
        $creator = $timeline->user;

        // find a user who is not yet a follower (includes subscribers) of timeline
        $fan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', $creator->id)->first();

        // Block the fan (note we do programatically, not via API as this is not integral to this test)
        DB::table('blockables')->insert([
            'blockable_type' => 'timelines',
            'blockable_id' => $timeline->id,
            'user_id' => $fan->id,
        ]);
        $timeline->refresh();
        $fan->refresh();

        // Try to follow
        $payload = [
            'sharee_id' => $fan->id,
            'notes'=>'test_can_not_follow_timeline',
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.follow', $timeline->id), $payload);
        $response->assertStatus(403);
    }

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_can_subscribe_to_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // includes subscribers
        $creator = $timeline->user;

        // Make sure creator's timeline is paid-only
        $timeline->is_follow_for_free = false;
        $timeline->price = $this->faker->randomNumber(3);
        $timeline->save();
        $timeline->refresh();
        $origSubscriberCount = $timeline->subscribers->count();

        // find a user who is not yet a follower (includes subscribers) of timeline
        $fan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        // Check access (before: should be denied)
        // [ ] %TODO: actually this is more complex: they can access the timeline if follower (default), but can only see a subset
        //     of posts on it before subscription (premium)
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('timelines.show', $timeline->slug));
        //$response = $this->actingAs($fan)->ajaxJSON('GET', route('timelines.show', $timeline->user->username));
        $response->assertStatus(200); // was 403 but see TODO above

        $payload = [
            'sharee_id' => $fan->id,
            'notes'=>'test_can_subscribe_to_timeline',
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.subscribe', $timeline->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->timeline);
        $this->assertEquals($timeline->id, $content->timeline->id);
        $this->assertTrue( $content->is_subscribed );
        $this->assertEquals( $origSubscriberCount+1, $content->subscriber_count );

        $timeline->refresh();
        $this->assertEquals('premium', $timeline->followers->find($fan->id)->pivot->access_level);
        $this->assertEquals('timelines', $timeline->followers->find($fan->id)->pivot->shareable_type);
        $this->assertTrue( $timeline->followers->contains( $fan->id ) );
        $this->assertTrue( $fan->followedtimelines->contains( $content->timeline->id ) );
        $this->assertTrue( $timeline->subscribers->contains( $fan->id ) );
        $this->assertTrue( $fan->subscribedtimelines->contains( $content->timeline->id ) );
        $this->assertTrue( $content->is_subscribed );

        // Check ledger
        $fanledger = Fanledger::where('fltype', PaymentTypeEnum::SUBSCRIPTION)
            ->where('purchaseable_type', 'timelines')
            ->where('purchaseable_id', $timeline->id)
            ->where('seller_id', $creator->id)
            ->where('purchaser_id', $fan->id)
            ->first();
        $this->assertNotNull($fanledger);
        $this->assertEquals(1, $fanledger->qty);
        $this->assertEquals(intval($timeline->price), $fanledger->base_unit_cost_in_cents);
        $this->assertTrue( $timeline->ledgersales->contains( $fanledger->id ) );
        $this->assertTrue( $fan->ledgerpurchases->contains( $fanledger->id ) );

        // Check access (after: should be allowed)
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('timelines.show', $timeline->slug));
        $response->assertStatus(200);
    }

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_can_unsubscribe_from_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // includes subscribers
        $creator = $timeline->user;

        // Make sure creator's timeline is paid-only
        $timeline->is_follow_for_free = false;
        $timeline->price = $this->faker->randomNumber(3);
        $timeline->save();
        $timeline->refresh();

        // find a user who is not yet a follower (includes subscribers) of timeline
        $fan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        // Subscribe
        $payload = [
            'sharee_id' => $fan->id,
            'notes'=>'test_can_unsubscribe_from_timeline',
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.subscribe', $timeline->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->timeline);
        $this->assertTrue( $content->is_subscribed );
        $timeline->refresh();
        $origSubscriberCount = $timeline->subscribers->count();

        // Check access (after subscribe: should be allowed)
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('timelines.show', $timeline->slug));
        $response->assertStatus(200);

        // Unsubscribe
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.subscribe', $timeline->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->timeline);
        $this->assertEquals($timeline->id, $content->timeline->id);
        $this->assertFalse( $content->is_subscribed );
        $this->assertEquals( $origSubscriberCount-1, $content->subscriber_count );

        $timeline->refresh();
        $this->assertFalse( $timeline->followers->contains( $fan->id ) );
        $this->assertFalse( $fan->followedtimelines->contains( $content->timeline->id ) );
        $this->assertFalse( $timeline->subscribers->contains( $fan->id ) );
        $this->assertFalse( $fan->subscribedtimelines->contains( $content->timeline->id ) );
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

    protected function attachMediafile(Post &$post, string $type='image') 
    {
        $fname = $this->faker->slug.'.jpg';
        Mediafile::create([
            'filename' => $fname,
            'mfname' => $fname,
            'mftype' => MediafileTypeEnum::POST,
            'mimetype' => 'image/jpeg',
            'orig_ext' => 'jpg',
            'orig_filename' => $fname,
            'resource_type' => 'posts',
            'resource_id' => $post->id,
        ]);
        return $post;
    }
}

