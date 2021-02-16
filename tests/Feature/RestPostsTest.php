<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use DB;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;
use App\Models\Fanledger;
use App\Models\Mediafile;
use App\Models\Post;
use App\Models\Timeline;
use App\Models\User;
use App\Enums\MediafileTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Enums\PostTypeEnum;

class RestPostsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group posts
     *  @group regression
     */
    // %TODO: filters, timelines (see posts I follow), etc
    public function test_can_index_posts()
    {
        //$this->seed(\Database\Seeders\TestDatabaseSeeder::class);
        $timeline = Timeline::has('posts','>=',1)->first(); 
        $creator = $timeline->user;

        $response = $this->actingAs($creator)->ajaxJSON('GET', route('posts.index'));
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->posts);
        $postsR = collect($content->posts);
        //dd($postsR, $postsR[0]);
        $this->assertGreaterThan(0, $postsR->count());
        //$this->assertNotNull($postsR[0]->description);
        //$this->assertNotNull($postsR[0]->timeline_id);
        $this->assertObjectHasAttribute('postable_id', $postsR[0]);
        $this->assertObjectHasAttribute('postable_type', $postsR[0]);
        $this->assertObjectHasAttribute('description', $postsR[0]);
        $this->assertEquals($postsR[0]->postable_type, 'timelines');
        $this->assertEquals($postsR[0]->postable_id, $timeline->id);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_can_show_my_post()
    {
        $timeline = Timeline::has('posts','>=',1)->first();
        $creator = $timeline->user;
        $post = $timeline->posts[0];

        $response = $this->actingAs($creator)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $postR = $content->post;
        $this->assertNotNull($postR->description);
        $this->assertNotNull($postR->postable_id);
        $this->assertEquals($postR->postable_id, $timeline->id);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_can_show_followed_timelines_post()
    {
        //$timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $timeline = Timeline::whereHas('posts', function($q1) {
            $q1->where('type', PostTypeEnum::FREE);
        })->has('followers','>=',1)->first();
        $creator = $timeline->user;
        $post = $timeline->posts()->where('type', PostTypeEnum::FREE)->first();
        $fan = $timeline->followers[0];

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $postR = $content->post;
        $this->assertObjectHasAttribute('postable_id', $postR);
        $this->assertEquals($postR->postable_id, $timeline->id);

        // %TODO: test can not show unfollowed timeline's post
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_can_store_text_only_post_on_my_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->first();
        $creator = $timeline->user;

        $payload = [
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $this->assertNotNull($content->post->description);
        $this->assertEquals($payload['description'], $content->post->description);
        $this->assertEquals(PostTypeEnum::FREE, $content->post->type);
        $this->assertEquals(0, $content->post->price);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_can_create_a_post_on_my_timeline_of_type_purchaseable_and_set_a_price()
    {
        $timeline = Timeline::has('posts','>=',1)->first();
        $creator = $timeline->user;

        $payload = [
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
            'type' => PostTypeEnum::PRICED,
            'price' => $this->faker->randomNumber(3),
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $this->assertNotNull($content->post->description);
        $this->assertEquals(PostTypeEnum::PRICED, $content->post->type);
        $this->assertEquals($payload['price'], $content->post->price);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_nonowner_can_not_store_post_on_my_timeline()
    {
        $timeline = Timeline::has('posts', '>=', 1)->first();
        $creator = $timeline->user;
        $nonowner = User::where('id', '<>', $creator->id)->first();

        $payload = [
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($nonowner)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(403);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_can_store_post_with_single_image_on_my_timeline()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('posts','>=',1)->first();
        $creator = $timeline->user;

        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $payload = [
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $postR = $content->post;

        // --

        $payload = [
            'mftype' => MediafileTypeEnum::POST,
            'mediafile' => $file,
            'resource_type' => 'posts',
            'resource_id' => $postR->id,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);

        $mediafile = Mediafile::where('resource_type', 'posts')->where('resource_id', $postR->id)->first();
        $this->assertNotNull($mediafile);
        Storage::disk('s3')->assertExists($mediafile->filename);
        $this->assertSame($filename, $mediafile->mfname);
        $this->assertSame(MediafileTypeEnum::POST, $mediafile->mftype);

        // Test relations
        $post = Post::find($postR->id);
        $this->assertNotNull($post);
        $this->assertTrue( $post->mediafiles->contains($mediafile->id) );
        $this->assertEquals( $post->id, $mediafile->resource->id );

    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_can_store_post_with_multiple_images_on_my_timeline()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('posts','>=',1)->first();
        $creator = $timeline->user;

        $filenames = [
            $this->faker->slug,
            $this->faker->slug,
        ];
        $files = [
            UploadedFile::fake()->image($filenames[0], 200, 200),
            UploadedFile::fake()->image($filenames[1], 200, 200),
        ];

        $payload = [
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $postR = $content->post;

        // --

        // 1st file
        $payload = [
            'mftype' => MediafileTypeEnum::POST,
            'mediafile' => $files[0],
            'resource_type' => 'posts',
            'resource_id' => $postR->id,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);

        // 2nd file
        $payload = [
            'mftype' => MediafileTypeEnum::POST,
            'mediafile' => $files[1],
            'resource_type' => 'posts',
            'resource_id' => $postR->id,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);

        $mediafiles = Mediafile::where('resource_type', 'posts')->where('resource_id', $postR->id)->get();
        $this->assertNotNull($mediafiles);
        $this->assertEquals(2, $mediafiles->count());
        Storage::disk('s3')->assertExists($mediafiles[0]->filename);
        Storage::disk('s3')->assertExists($mediafiles[1]->filename);
        $this->assertSame($filenames[0], $mediafiles[0]->mfname);
        $this->assertSame($filenames[1], $mediafiles[1]->mfname);
        $this->assertSame(MediafileTypeEnum::POST, $mediafiles[0]->mftype);
        $this->assertSame(MediafileTypeEnum::POST, $mediafiles[1]->mftype);

        // Test relations
        $post = Post::find($postR->id);
        $this->assertNotNull($post);
        $this->assertTrue( $post->mediafiles->contains($mediafiles[0]->id) );
        $this->assertEquals( $post->id, $mediafiles[0]->resource->id );
        $this->assertTrue( $post->mediafiles->contains($mediafiles[1]->id) );
        $this->assertEquals( $post->id, $mediafiles[1]->resource->id );
    }

    /**
     *  @group posts
     *  @group regression
     *  [ ] can not delete a PRICED post that others have purchased
     *  [ ] can edit/delete any post if no one has purchased (ie ledger balance of 0)
     */
    public function test_owner_can_edit_free_post()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->first();
        $creator = $timeline->user;
        $post = $timeline->posts->where('type', PostTypeEnum::FREE)->first();
        $origDesc = $post->description;

        $payload = [
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('posts.update', $post->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $this->assertNotNull($content->post->description);
        $this->assertNotEquals($origDesc, $content->post->description);
        $this->assertEquals($payload['description'], $content->post->description);
    }

    /**
     *  @group posts
     *  @group regression
     *  @group here
     */
    public function test_nonowner_can_non_edit_free_post()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->first();
        $creator = $timeline->user;
        $post = $timeline->posts->where('type', PostTypeEnum::FREE)->first();
        $origDesc = $post->description;
        $nonowner = User::where('id', '<>', $creator->id)->first();

        $payload = [
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($nonowner)->ajaxJSON('PATCH', route('posts.update', $post->id), $payload);
        $response->assertStatus(403);
    }


    /**
     *  @group posts
     *  @group regression
     */
    public function test_owner_can_delete_free_post()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->first();
        $creator = $timeline->user;
        $post = $timeline->posts->where('type', PostTypeEnum::FREE)->first();

        $response = $this->actingAs($creator)->ajaxJSON('DELETE', route('posts.destroy', $post->id));
        $response->assertStatus(200);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_follower_can_view_free_post_on_my_timeline()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->firstOrFail();
        $creator = $timeline->user;
        $post = $timeline->posts->where('type', PostTypeEnum::FREE)->first();
        $fan = $timeline->followers[0];
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_nonfollower_can_not_view_free_post_on_my_timeline()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->firstOrFail();
        $creator = $timeline->user;
        $post = $timeline->posts->where('type', PostTypeEnum::FREE)->first();
        $nonfan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        $response = $this->actingAs($nonfan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(403);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_subscriber_can_view_subcribe_only_post_on_my_timeline()
    {
        // %FIXME: ensure at least one subscriber... (hardcode)
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::SUBSCRIBER);
            })->firstOrFail();
        $creator = $timeline->user;
        $post = $timeline->posts->where('type', PostTypeEnum::SUBSCRIBER)->first();
        $fan = $timeline->followers[0];

        // [ ] set to premium/subscribe...workaround until shareables seeder is fixed
        // to guarantee some subscribers not just followers
        DB::table('shareables')
            ->where('sharee_id', $fan->id)
            ->where('shareable_type', 'timelines')
            ->where('shareable_id', $timeline->id)
            ->update([
                'access_level' => 'premium',
            ]);
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_follower_can_view_image_of_free_post_on_my_timeline()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;

        // --- Create a free post with an image ---

        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);
        $payload = [
            'type' => PostTypeEnum::FREE,
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $postR = $content->post;
        $payload = [
            'mftype' => MediafileTypeEnum::POST,
            'mediafile' => $file,
            'resource_type' => 'posts',
            'resource_id' => $postR->id,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);

        // --

        $timeline->refresh();
        $creator->refresh();
        $post = $timeline->posts()->has('mediafiles', '>=', 1)
                         ->where('type', PostTypeEnum::FREE)
                         ->first();
        $mediafile = $post->mediafiles->shift();

        $fan = $timeline->followers[0];
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('mediafiles.show', $mediafile->id));
        $response->assertStatus(200);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_nonfollower_can_not_view_image_of_free_post_on_my_timeline()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;

        // --- Create a free post with an image ---

        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);
        $payload = [
            'type' => PostTypeEnum::FREE,
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $postR = $content->post;
        $payload = [
            'mftype' => MediafileTypeEnum::POST,
            'mediafile' => $file,
            'resource_type' => 'posts',
            'resource_id' => $postR->id,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);

        // --

        $timeline->refresh();
        $creator->refresh();
        $nonFan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', '<>', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        //$this->assertNotEquals($nonFan->id, $creator->id);
        //$this->assertFalse($timeline->followers->contains($nonFan->id));

        $post = $timeline->posts()->has('mediafiles', '>=', 1)
                         ->where('type', PostTypeEnum::FREE)
                         ->first();
        $mediafile = $post->mediafiles->shift();

        $response = $this->actingAs($nonFan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(403);

        $response = $this->actingAs($nonFan)->ajaxJSON('GET', route('mediafiles.show', $mediafile->id));
        $response->assertStatus(403);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_can_update_my_post()
    {
        $timeline = Timeline::has('posts','>=',1)->first(); 
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
     *  @group OFF-posts
     *  @group OFF-regression
     *  @group todo
     */
    public function test_can_store_post_on_followed_timeline()
    {
        // post on followed timeline (not my own)
        //$response->assertStatus(200);
    }

    /**
     *  @group OFF-posts
     *  @group OFF-regression
     *  @group TODO
     */
    public function test_can_store_post_with_image_on_followed_timeline()
    {
        // post on followed timeline (not my own)
        //$response->assertStatus(200);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_can_destroy_my_post()
    {
        $timeline = Timeline::has('posts','>=',1)->first();
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
     *  @group posts
     *  @group regression
     */
    public function test_timeline_follower_can_like_then_unlike_post()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->first();
        $creator = $timeline->user;
        $post = $timeline->posts->where('type', PostTypeEnum::FREE)->first();
        $fan = $timeline->followers[0];

        // remove any existing likes by fan...
        DB::table('likeables')
            ->where('likee_id', $fan->id)
            ->where('likeable_type', 'posts')
            ->where('likeable_id', $post->id)
            ->delete();

        // LIKE the post
        $payload = [
            'likeable_type' => 'posts',
            'likeable_id' => $post->id,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('likeables.update', $fan->id), $payload); // fan->likee
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

        // UNLIKE the post
        $payload = [
            'likeable_type' => 'posts',
            'likeable_id' => $post->id,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('DELETE', route('likeables.destroy', $fan->id), $payload); // fan->likee
        $response->assertStatus(200);

        $likeable = DB::table('likeables')
            ->where('likee_id', $fan->id)
            ->where('likeable_type', 'posts')
            ->where('likeable_id', $postR->id)
            ->first();
        $this->assertNull($likeable);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_can_send_tip_on_post()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $creator = $timeline->user;
        $post = $timeline->posts[0];
        $fan = $timeline->followers[0];

        $payload = [
            'base_unit_cost_in_cents' => $this->faker->randomNumber(3),
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('posts.tip', $post->id), $payload);

        $response->assertStatus(200);

        $content = json_decode($response->content());
        $postR = $content->post;

        $fanledger = Fanledger::where('fltype', PaymentTypeEnum::TIP)->latest()->first();
        $this->assertNotNull($fanledger);
        $this->assertEquals(1, $fanledger->qty);
        $this->assertEquals($payload['base_unit_cost_in_cents'], $fanledger->base_unit_cost_in_cents);
        $this->assertEquals(PaymentTypeEnum::TIP, $fanledger->fltype);
        $this->assertEquals($fan->id, $fanledger->purchaser_id);
        $this->assertEquals($creator->id, $fanledger->seller_id);
        $this->assertEquals('posts', $fanledger->purchaseable_type);
        $this->assertEquals($post->id, $fanledger->purchaseable_id);
        $this->assertTrue( $post->ledgersales->contains( $fanledger->id ) );
        $this->assertTrue( $fan->ledgerpurchases->contains( $fanledger->id ) );
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_can_purchase_post()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::PRICED);
            })->first();
        $creator = $timeline->user;
        $fan = $timeline->followers[0];

        // Store a new post just to ensure it's not already shared with the fan...
        $payload = [
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
            'type' => PostTypeEnum::PRICED,
            'price' => $this->faker->randomNumber(3),
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $post = Post::find($content->post->id);
        $this->assertNotNull($post);
        //$this->assertFalse( $fan->sharedposts->contains($post->id) ); // not yet shared w/ fan

        // Check access (before: should be denied)
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(403);

        $payload = [
            'sharee_id' => $fan->id,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('posts.purchase', $post->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $postR = $content->post;

        // Check ledger
        $fanledger = Fanledger::where('fltype', PaymentTypeEnum::PURCHASE)->latest()->first();
        $this->assertNotNull($fanledger);
        $this->assertEquals(1, $fanledger->qty);
        $this->assertEquals($post->price*100, $fanledger->base_unit_cost_in_cents);
        $this->assertEquals(PaymentTypeEnum::PURCHASE, $fanledger->fltype);
        $this->assertEquals($fan->id, $fanledger->purchaser_id);
        $this->assertEquals($creator->id, $fanledger->seller_id);
        $this->assertEquals('posts', $fanledger->purchaseable_type);
        $this->assertEquals($post->id, $fanledger->purchaseable_id);
        $this->assertTrue( $post->sharees->contains( $fan->id ) );
        $this->assertTrue( $post->ledgersales->contains( $fanledger->id ) );
        $this->assertTrue( $fan->ledgerpurchases->contains( $fanledger->id ) );

        // Check access (after: should be allowed)
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);
    }

    /**
     *  @group posts
     *  @group regression
     */
    public function test_owner_can_not_edit_a_priced_post_that_others_have_purchased()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::PRICED);
            })->first();
        $creator = $timeline->user;
        $fan = $timeline->followers[0];

        // Store a new post just to ensure it's not already shared with the fan...
        $payload = [
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
            'type' => PostTypeEnum::PRICED,
            'price' => $this->faker->randomNumber(3),
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());

        // Fan purchases post resulting in a share...
        $payload = [
            'sharee_id' => $fan->id,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('posts.purchase', $content->post->id), $payload);
        $response->assertStatus(200);

        // Owner attempts update
        $payload = [
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('posts.update', $content->post->id), $payload);
        $response->assertStatus(403);
    }

    /**
     *  @group posts
     *  @group regression
     */
    // priced: one-time-purchaseable, as opposed to subscribeable
    public function test_owner_can_not_delete_a_priced_post_that_others_have_purchased()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::PRICED);
            })->first();
        $creator = $timeline->user;
        $fan = $timeline->followers[0];

        // Store a new post just to ensure it's not already shared with the fan...
        $payload = [
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
            'type' => PostTypeEnum::PRICED,
            'price' => $this->faker->randomNumber(3),
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());

        // Fan purchases post resulting in a share...
        $payload = [
            'sharee_id' => $fan->id,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('posts.purchase', $content->post->id), $payload);
        $response->assertStatus(200);

        // Owner attempts delete
        $response = $this->actingAs($creator)->ajaxJSON('DELETE', route('posts.destroy', $content->post->id));
        $response->assertStatus(403);
    }

    /**
     *  @group TODO-posts
     */
    public function test_can_share_post()
    {
    }

    /**
     *  @group TODO-posts
     */
    public function test_can_pin_post()
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

