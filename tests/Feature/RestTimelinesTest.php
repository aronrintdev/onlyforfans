<?php
namespace Tests\Feature;

use DB;
use App\Models\Tip;
use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\Timeline;
use App\Events\TipFailed;
use App\Models\Mediafile;
use App\Events\ItemTipped;
use App\Enums\PostTypeEnum;
use App\Models\Diskmediafile;
use App\Enums\PaymentTypeEnum;
use App\Events\ItemSubscribed;
use Illuminate\Support\Carbon;
use App\Enums\MediafileTypeEnum;
use App\Events\SubscriptionFailed;
use App\Models\Financial\SegpayCard;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use App\Enums\Financial\AccountTypeEnum;
use Database\Seeders\TestDatabaseSeeder;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group feature
 * @group timelines
 *
 * @package Tests\Feature
 */
class RestTimelinesTest extends TestCase
{
    use WithFaker;
    //use RefreshDatabase;

    /**
     *  @group timelines
     *  @group regression
     *  @group regression-base
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
     *  @group regression-base
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
     *  @group regression-base
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
     *  @group regression-base
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
     *  @group regression
     *  @group regression-base
     */
    public function test_owner_can_view_queued_feed()
    {
        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;

        // Shows no scheduled posts
        $payload = [];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('timelines.homescheduledfeed'), $payload);
        $response->assertStatus(200);
        $response->assertJson([ 'meta' => [ 'total' => 0 ] ]);

        // Schedule a post
        $post = $timeline->posts()->create([
            'user_id' => $creator->id,
            'active' => true,
            'type' => PostTypeEnum::FREE,
            'description' => $this->faker->realText(),
            'schedule_datetime' => Carbon::now()->addHours(4),
        ]);

        $payload = [];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('timelines.homescheduledfeed'), $payload);
        $response->assertStatus(200);
        $response->assertJson([ 'meta' => ['total' => 1]]);

        $post->delete();
    }

    /**
     *  @group regression
     *  @group regression-base
     */
    public function test_non_admin_can_not_view_all_queued_feed_items()
    {
        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $otherTimeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->where('id', '!=', $timeline->id)->first();
        $creator = $timeline->user;

        // Can't see other user's scheduled posts
        $post = $otherTimeline->posts()->create([
            'user_id' => $otherTimeline->user->id,
            'active' => true,
            'type' => PostTypeEnum::FREE,
            'description' => $this->faker->realText(),
            'schedule_datetime' => Carbon::now()->addHours(4),
        ]);

        $payload = [];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('timelines.homescheduledfeed'), $payload);
        $response->assertStatus(200);
        $response->assertJson([ 'meta' => ['total' => 0]]);

        $post->delete();
    }

    /**
     *  @group timelines
     *  @group regression
     *  @group regression-base
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
     *  @group regression-base
     */
    public function test_fan_can_not_access_locked_content_via_feed()
    {
        $timeline = Timeline::has('posts','>=',5)->has('followers','>=',1)->firstOrFail(); // assume non-admin (%FIXME)

        // Makes sure we have at least 1 free, 1 priced, and 1 subscibe-only post, then add some mediafiles to the posts...
        /*
        $posts = Post::where('postable_type', 'timelines')
            ->has('mediafiles', 0)
            ->where('postable_id', $timeline->id)->take(3)->get();
         */
        $posts = Post::where('postable_type', 'timelines')->has('mediafiles', 0)->take(3)->get();

        // Setup posts for this test specifically...
        $freePost = $posts[0];
        $freePost->type = PostTypeEnum::FREE;
        $freePost->postable_id = $timeline->id;
        $freePost->postable_type = 'timelines';
        $freePost->save();
        $this->associateMediafile($freePost);
        $this->associateMediafile($freePost);

        $pricedPost = $posts[1];
        $pricedPost->type = PostTypeEnum::PRICED;
        $pricedPost->postable_id = $timeline->id;
        $pricedPost->postable_type = 'timelines';
        $pricedPost->price = 7*100;
        $pricedPost->save();
        $this->associateMediafile($pricedPost);
        $this->associateMediafile($pricedPost);

        $subPost = $posts[2];
        $subPost->type = PostTypeEnum::SUBSCRIBER;
        $subPost->postable_id = $timeline->id;
        $subPost->postable_type = 'timelines';
        $subPost->save();
        $this->associateMediafile($subPost);
        $this->associateMediafile($subPost);

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
        $savedPostIDs = [
            'free' => $freePost->id,
            'priced' => $pricedPost->id,
            'sub' => $subPost->id,
        ];
        unset($freePost, $pricedPost, $subPost);

        $posts = collect($content->data);

        /*
        $freePost = $posts->first( function($p) use($savedPostIDs) {
            //return $p->type === PostTypeEnum::FREE;
            return $p->id === $savedPostIDs['free'];
        });
         */
        $freePost = $posts->firstWhere('id', $savedPostIDs['free']);
        $this->assertNotNull($freePost);
        $this->assertEquals(2, $freePost->mediafile_count);
        $this->assertTrue($freePost->access);
        $this->assertNotNull($freePost->mediafiles[0]);
        $this->assertNotNull($freePost->mediafiles[0]->filepath);

        /*
        $pricedPost = $posts->first( function($p) use($savedPostIDs) {
            //return $p->type === PostTypeEnum::PRICED;
            return $p->id === $savedPostIDs['priced'];
        });
         */
        $pricedPost = $posts->firstWhere('id', $savedPostIDs['priced']);
        $this->assertNotNull($pricedPost);
        $this->assertEquals(2, $pricedPost->mediafile_count);
        $this->assertFalse($pricedPost->access);
        $this->assertNotNull($pricedPost->mediafiles[0]);
        $this->assertNull($pricedPost->mediafiles[0]->filepath); // can't access media!

        /*
        $subPost = $posts->first( function($p) use($savedPostIDs) {
            //return $p->type === PostTypeEnum::SUBSCRIBER;
            return $p->id === $savedPostIDs['sub'];
        });
         */
        $subPost = $posts->firstWhere('id', $savedPostIDs['sub']);
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
     *  @group regression-base
     */
    public function test_fan_can_view_photos_only_feed()
    {
        $timeline = Timeline::has('posts','>=',5)->has('followers','>=',1)->firstOrFail();
        $creator = $timeline->user;
        $fan = $timeline->followers[0];

        // Add some mediafiles (photos) to the posts...
        $posts = Post::where('postable_type', 'timelines')->where('postable_id', $timeline->id)->latest()->take(5)->get();
        $this->associateMediafile($posts[0]);
        $this->associateMediafile($posts[0]);
        $this->associateMediafile($posts[1]);
        $this->associateMediafile($posts[2]);
        $this->associateMediafile($posts[2]);
        $this->associateMediafile($posts[2]);
        $this->associateMediafile($posts[3]);

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('timelines.photos', $timeline->id), []);
        $response->assertStatus(200);
        $content = json_decode($response->content());
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
        $mediafiles = collect($content->data);
        //dd($content);
    }


    /**
     *  @group timelines
     *  @group regression
     *  @group regression-base
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
        $this->assertObjectHasAttribute('user', $content->data);
        $this->assertNotNull($content->data->user->id);

        $this->assertObjectHasAttribute('user', $content->data);
        $this->assertObjectHasAttribute('id', $content->data->user);
        // Only want user id
        //$this->assertObjectNotHasAttribute('username', $content->data->user);
        $this->assertObjectNotHasAttribute('email', $content->data->user);
        $this->assertObjectNotHasAttribute('password', $content->data->user);
        $this->assertObjectNotHasAttribute('remember_token', $content->data->user);
        $this->assertObjectNotHasAttribute('verification_code', $content->data->user);

        $this->assertObjectNotHasAttribute('posts', $content->data);
        $this->assertObjectNotHasAttribute('followers', $content->data);
        $this->assertObjectNotHasAttribute('subscribers', $content->data);
        //$this->assertObjectNotHasAttribute('stories', $content->data);
    }

    /**
     *  @group timelines
     *  @group regression
     *  @group regression-base
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

        $this->assertObjectHasAttribute('user', $content->data);
        $this->assertObjectHasAttribute('id', $content->data->user);
        // Only want user id
        $this->assertObjectNotHasAttribute('username', $content->data->user);
        $this->assertObjectNotHasAttribute('email', $content->data->user);
        $this->assertObjectNotHasAttribute('password', $content->data->user);
        $this->assertObjectNotHasAttribute('remember_token', $content->data->user);
        $this->assertObjectNotHasAttribute('verification_code', $content->data->user);

        $this->assertObjectHasAttribute('cover', $content->data);
        $this->assertObjectHasAttribute('avatar', $content->data);
        $this->assertObjectNotHasAttribute('posts', $content->data);
        $this->assertObjectNotHasAttribute('followers', $content->data);
        $this->assertObjectNotHasAttribute('subscribers', $content->data);
        //$this->assertObjectNotHasAttribute('stories', $content->data);
    }

    /**
     *  @group timelines
     *  @group regression
     *  @group regression-base
     *  @group june29
     */
    public function test_can_view_suggested_timelines()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->firstOrFail(); // assume non-admin (%FIXME)
        $creator = $timeline->user;
        $nonfan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->firstOrFail();

        $payload = [];
        $response = $this->actingAs($nonfan)->ajaxJSON('GET', route('timelines.suggested'), $payload);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        //dd($content);
        $response->assertJsonStructure([
            'data' => [0 => [ 'id', 'slug', 'name', 'about', 'verified', 'price', 'is_follow_for_free', 'cover', 'avatar', ] ]
        ]);
    }

    /**
     *  @group timelines
     *  @group regression
     *  @group regression-base
     *  @group erik
     */
    public function test_can_send_tip_to_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $creator = $timeline->user;
        $fan = $timeline->followers[0];

        $events = Event::fake([
            ItemTipped::class,
            TipFailed::class,
        ]);

        $account = $fan->financialAccounts()->where('type', AccountTypeEnum::IN)->with('resource')->first();
        $payload = [
            'amount' => $this->faker->numberBetween(1, 20) * 500,
            'currency' => 'USD',
            'account_id' => $account->getKey(),
            'message' => $this->faker->text(),
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.tip', $timeline->id), $payload);
        $response->assertStatus(200);

        // ItemPurchased will update client with websocket event
        Event::assertDispatched(ItemTipped::class);
        Event::assertNotDispatched(TipFailed::class);

        $content = json_decode($response->content());

        // Tip was added to the database
        $this->assertDatabaseHas(Tip::getTableName(), [
            'sender_id'   => $fan->getKey(),
            'receiver_id' => $creator->getKey(),
            'tippable_id' => $timeline->getKey(),
            'account_id'  => $account->getKey(),
            'message'     => $payload['message'],
            'amount'      => $payload['amount'],
        ]);

        // Amount from Card
        $this->assertDatabaseHas('transactions', [
            'account_id'   => $account->getKey(),
            'debit_amount' => $payload['amount'],
        ], 'financial');

        // Amount from fan to creator
        $this->assertDatabaseHas('transactions', [
            'account_id'   => $fan->getWalletAccount('segpay', 'USD')->getKey(),
            'debit_amount' => $payload['amount'],
        ], 'financial');

        // Amount to creator from fan
        $this->assertDatabaseHas('transactions', [
            'account_id'    => $creator->getEarningsAccount('segpay', 'USD')->getKey(),
            'credit_amount' => $payload['amount'],
        ], 'financial');
    }

    /**
     *  @group timelines
     *  @group regression
     *  @group regression-base
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
     *  @group regression-base
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
     *  @group OFF-regression
     *  @group broken
     */
    /*
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
     */

    /**
     *  @group timelines
     *  @group regression
     *  @group regression-base
     *  @group erik
     */
    public function test_can_subscribe_to_timeline()
    {
        $this->assertTrue(Config::get('segpay.fake'), 'Your SEGPAY_FAKE .env variable is not set to true.');

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

        $events = Event::fake([
            ItemSubscribed::class,
            SubscriptionFailed::class
        ]);
        $account = $fan->financialAccounts()->where('type', AccountTypeEnum::IN)->with('resource')->first();
        $payload = [
            'account_id' => $account->getKey(),
            'amount'     => $timeline->price->getAmount(),
            'currency'   => $timeline->currency,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.subscribe', ['timeline' => $timeline->id]), $payload);
        $response->assertStatus(200);

        Event::assertDispatched(ItemSubscribed::class);
        Event::assertNotDispatched(SubscriptionFailed::class);

        // Check transactions ledger

        // Amount from Card
        $this->assertDatabaseHas('transactions', [
            'account_id' => $account->getKey(),
            'debit_amount' => $timeline->price->getAmount(),
        ], 'financial');

        // Amount from fan to creator
        $this->assertDatabaseHas('transactions', [
            'account_id' => $fan->getWalletAccount('segpay', 'USD')->getKey(),
            'debit_amount' => $timeline->price->getAmount(),
            'resource_id' => $timeline->getKey(),
        ], 'financial');

        // Amount to creator from fan
        $this->assertDatabaseHas('transactions', [
            'account_id' => $creator->getEarningsAccount('segpay', 'USD')->getKey(),
            'credit_amount' => $timeline->price->getAmount(),
            'resource_id' => $timeline->getKey(),
        ], 'financial');

        // Fan has access
        $timeline->refresh();
        $this->assertEquals('premium', $timeline->followers->find($fan->id)->pivot->access_level);
        $this->assertEquals('timelines', $timeline->followers->find($fan->id)->pivot->shareable_type);
        $this->assertTrue( $timeline->followers->contains( $fan->id ) );
        $this->assertTrue( $fan->followedtimelines->contains( $timeline->id ) );
        $this->assertTrue( $timeline->subscribers->contains( $fan->id ) );
        $this->assertTrue( $fan->subscribedtimelines->contains( $timeline->id ) );

        // Check access (after: should be allowed)
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('timelines.show', $timeline->slug));
        $response->assertStatus(200);
    }

    /**
     *  @group timelines
     *  @group regression
     *  @group regression-base
     *  @group erik
     */
    public function test_can_unsubscribe_from_timeline()
    {
        $this->assertTrue(Config::get('segpay.fake'), 'Your SEGPAY_FAKE .env variable is not set to true.');

        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // includes subscribers
        $creator = $timeline->user;

        // Make sure creator's timeline is paid-only
        $timeline->is_follow_for_free = false;
        $timeline->price = $this->faker->numberBetween(300, 4000);
        $timeline->currency = 'USD';
        $timeline->save();
        $timeline->refresh();

        // find a user who is not yet a follower (includes subscribers) of timeline
        $fan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        $cardNickname = $this->faker->realText(20);

        // Subscribe
        $payload = [
            // 'sharee_id' => $fan->id,
            'item'     => $timeline->getKey(),
            'type'     => PaymentTypeEnum::SUBSCRIPTION,
            'price'    => $timeline->price->getAmount(),
            'currency' => $timeline->currency,
            'last_4'   => '1111',
            'brand'    => 'visa',
            'nickname' => $cardNickname,
        ];

        $events = Event::fake([
            ItemSubscribed::class,
            SubscriptionFailed::class,
        ]);
        $account = $fan->financialAccounts()->where('type', AccountTypeEnum::IN)->with('resource')->first();
        $payload = [
            'account_id' => $account->getKey(),
            'amount'     => $timeline->price->getAmount(),
            'currency'   => $timeline->currency,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.subscribe', ['timeline' => $timeline->id]), $payload);
        $response->assertStatus(200);

        // ItemPurchased will update client with websocket event
        Event::assertDispatched(ItemSubscribed::class);
        Event::assertNotDispatched(SubscriptionFailed::class);

        $timeline->refresh();
        $origSubscriberCount = $timeline->subscribers->count();

        // Check access (after subscribe: should be allowed)
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('timelines.show', $timeline->slug));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->data);
        $this->assertEquals($timeline->id, $content->data->id);
        $this->assertTrue($content->data->is_subscribed);

        // Unsubscribe
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.unsubscribe', $timeline->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->daysRemaining);

        // Should still have access until time remaining is up.
        $this->assertNotNull($content->timeline);
        $this->assertEquals($timeline->id, $content->timeline->id);
        $this->assertTrue($content->timeline->is_subscribed);

        // Time travel
        $this->travel($content->daysRemaining + 1)->days();
        // Run subscription checking script
        $this->artisan('subscription:update-canceled');

        // Check for removed from access
        $timeline->refresh();
        $fan->refresh();
        $this->assertFalse( $timeline->subscribers->contains( $fan->id ) );
        $this->assertFalse( $fan->subscribedtimelines->contains( $content->timeline->id ) );

        $this->travelBack();
    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
        //$this->seed(TestDatabaseSeeder::class);
    }

    protected function tearDown() : void {
        parent::tearDown();
    }

    protected function associateMediafile(Post &$post, string $type='image') 
    {
        $fname = $this->faker->slug.'.jpg';
        Diskmediafile::doCreate([
                            'owner_id'        => $post->user->id,
                            'filepath'        => $fname,
                            'mimetype'        => 'image/jpeg',
                            'orig_filename'   => $fname,
                            'orig_ext'        => 'jpg',
                            'mfname'          => $fname,
                            'mftype'          => MediafileTypeEnum::POST,
                            'resource_id'     => $post->id,
                            'resource_type'   => 'posts',

        ]);
        return $post;
    }
}

