<?php
namespace Tests\Feature;

use DB;
use Tests\TestCase;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\Likeable;
use App\Models\Timeline;
use App\Events\TipFailed;

use App\Models\Mediafile;
use Illuminate\Http\File;

use App\Events\ItemTipped;
use App\Enums\PostTypeEnum;
use App\Events\ItemPurchased;
use App\Enums\PaymentTypeEnum;
use App\Events\PurchaseFailed;
use App\Enums\MediafileTypeEnum;
use Illuminate\Http\UploadedFile;
use App\Models\Financial\SegpayCard;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use App\Enums\Financial\AccountTypeEnum;
use App\Models\Tip;
use Database\Seeders\TestDatabaseSeeder;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Foundation\Testing\RefreshDatabase;

class RestPostsTest extends TestCase
{
    use WithFaker;

    /**
     *  @group posts
     *  @group regression
     *  @group regression-base
     */
    // %TODO: filters, timelines (see posts I follow), etc
    public function test_can_list_posts()
    {
        //$this->seed(\Database\Seeders\TestDatabaseSeeder::class);
        $timeline = Timeline::has('posts','>=',1)->first(); 
        $creator = $timeline->user;
        $expectedCount = Post::where('postable_type', 'timelines')
            ->where('postable_id', $timeline->id)
            ->count();

        $response = $this->actingAs($creator)->ajaxJSON('GET', route('posts.index'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);

        $content = json_decode($response->content());
        $this->assertEquals(1, $content->meta->current_page);
        $this->assertNotNull($content->data);
        $this->assertGreaterThan(0, count($content->data));
        $this->assertEquals($expectedCount, count($content->data));
        $this->assertEquals($timeline->posts->count(), count($content->data));
        $this->assertObjectHasAttribute('postable_id', $content->data[0]);
        $this->assertEquals($timeline->id, $content->data[0]->postable_id);
        $this->assertObjectHasAttribute('postable_type', $content->data[0]);
        $this->assertEquals('timelines', $content->data[0]->postable_type);
        $this->assertObjectHasAttribute('description', $content->data[0]);
    }

    /**
     *  @group posts
     *  @group regression-base
     */
    public function test_owner_can_view_own_post()
    {
        $timeline = Timeline::has('posts','>=',1)->first();
        $creator = $timeline->user;

        $post = $timeline->posts->where('type', PostTypeEnum::FREE)->first();
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->data);
        $this->assertObjectHasAttribute('description', $content->data);
        $this->assertObjectHasAttribute('slug', $content->data);
        $this->assertObjectHasAttribute('postable_id', $content->data);
        $this->assertObjectHasAttribute('postable_type', $content->data);
        $this->assertNotNull($content->data->description);
        $this->assertNotNull($content->data->slug);
        $this->assertNotNull($content->data->postable_id);
        $this->assertEquals($timeline->id, $content->data->postable_id);
        $this->assertSame('timelines', $content->data->postable_type);

        $post = $timeline->posts->where('type', PostTypeEnum::PRICED)->first();
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);

        $post = $timeline->posts->where('type', PostTypeEnum::SUBSCRIBER)->first();
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);
    }

    /**
     *  @group posts
     *  @group regression-base
     */
    public function test_can_view_followed_timelines_post()
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
        $this->assertNotNull($content->data);
        $this->assertObjectHasAttribute('postable_id', $content->data);
        $this->assertEquals($content->data->postable_id, $timeline->id);

        // %TODO: test can not show unfollowed timeline's post
    }

    /**
     *  @group posts
     *  @group regression
     *  @group regression-base
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
     *  @group regression-base
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
     *  @group regression-base
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
     *  @group regression-base
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
     *  @group regression-base
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

        // attach 1st file
        $payload = [
            'mftype' => MediafileTypeEnum::POST,
            'mediafile' => $files[0],
            'resource_type' => 'posts',
            'resource_id' => $postR->id,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);

        // attach 2nd file
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
     *  @group regression-base
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
     *  @group regression-base
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
     *  @group regression-base
     */
    public function test_owner_can_delete_free_post()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->firstOrFail();
        $creator = $timeline->user;
        $this->assertNotNull($creator);

        $post = Post::where('postable_type', 'timelines')
            ->where('postable_id', $timeline->id)
            ->has('likes','>',0)
            ->has('comments','>',0)
            ->has('mediafiles','>',0)
            ->doesntHave('sharees')
            ->firstorFail();
        $numLikes = Likeable::where('likeable_type', 'posts')->where('likeable_id', $post->id)->count();
        $this->assertGreaterThan(0, $numLikes);
        $numComments = Comment::where('commentable_type', 'posts')->where('commentable_id', $post->id)->count();
        $this->assertGreaterThan(0, $numComments);
        $numMediafiles = Mediafile::where('resource_type', 'posts')->where('resource_id', $post->id)->count();
        $this->assertGreaterThan(0, $numMediafiles);

        $response = $this->actingAs($creator)->ajaxJSON('DELETE', route('posts.destroy', $post->id));
        $response->assertStatus(200);

        $exists = Post::find($post->id);
        $this->assertNull($exists); // deleted

        // Test relation cleanup
        $numLikes = Likeable::where('likeable_type', 'posts')->where('likeable_id', $post->id)->count();
        $this->assertEquals(0, $numLikes);
        $numComments = Comment::where('commentable_type', 'posts')->where('commentable_id', $post->id)->count();
        $this->assertEquals(0, $numComments);
        $numMediafiles = Mediafile::where('resource_type', 'posts')->where('resource_id', $post->id)->count();
        $this->assertEquals(0, $numMediafiles);

        // ---

        $timeline2 = Timeline::find($timeline->id);
        $this->assertNotNull($timeline);
        $this->assertNotNull($timeline->user);

        // %TODO: test
        //  ~ related likes cleaned up
        //  ~ commments cleaned up
        //  ~  etc
        $bad = Timeline::doesntHave('user')->get(); // make sure no users got deleted (ie no timelines w/o corresponding user)
        $this->assertEquals(0, $bad->count());
    }

    /**
     *  @group posts
     *  @group regression
     *  @group regression-base
     */
    public function test_follower_can_view_free_post_on_my_timeline()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->firstOrFail();
        $this->assertNotNull($timeline);
        //dd($timeline);
        $creator = $timeline->user;
        $this->assertNotNull($creator);
        $post = $timeline->posts->where('type', PostTypeEnum::FREE)->first();
        $fan = $timeline->followers[0];
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);
    }

    /**
     *  @group posts
     *  @group regression
     *  @group regression-base
     */
    public function test_nonfollower_can_view_free_post_on_my_timeline()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->firstOrFail();
        $creator = $timeline->user;
        $this->assertNotNull($creator);

        $post = $timeline->posts->where('type', PostTypeEnum::FREE)->first();
        $nonfan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        $response = $this->actingAs($nonfan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);
    }

    /**
     *  @group posts
     *  @group regression
     *  @group regression-base
     */
    public function test_subscriber_can_view_subcribe_only_post_on_my_timeline()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('subscribers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::SUBSCRIBER);
            })->firstOrFail();
        $creator = $timeline->user;
        $this->assertNotNull($creator);
        $fan = $timeline->subscribers[0];

        // --

        // Create a subscribe-only post...
        $payload = [
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
            'type' => PostTypeEnum::SUBSCRIBER,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $post = Post::find($content->post->id);

        $filenames = [
            $this->faker->slug,
        ];
        $files = [
            UploadedFile::fake()->image($filenames[0], 200, 200),
        ];
        // attach 1st file
        $payload = [
            'mftype' => MediafileTypeEnum::POST,
            'mediafile' => $files[0],
            'resource_type' => 'posts',
            'resource_id' => $post->id,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);

        /* ?? is this necessary? Posts are subscribe-only or not
        // [ ] set to premium/subscribe...workaround until shareables seeder is fixed
        // to guarantee some subscribers not just followers
        DB::table('shareables')
            ->where('sharee_id', $fan->id)
            ->where('shareable_type', 'timelines')
            ->where('shareable_id', $timeline->id)
            ->update([
                'access_level' => 'premium',
            ]);
         */
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->data);

        $this->assertObjectHasAttribute('description', $content->data);
        $this->assertNotNull($content->data->description);
        $this->assertObjectHasAttribute('mediafiles', $content->data);
        $this->assertNotNull($content->data->mediafiles);
        $this->assertEquals(1, count($content->data->mediafiles));
        $this->assertEquals($filenames[0], $content->data->mediafiles[0]->mfname);
    }

    /**
     *  @group posts
     *  @group regression
     *  @group regression-base
     */
    public function test_nonsubscribed_follower_can_not_view_subcribe_only_post_content_on_my_timeline()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::SUBSCRIBER);
            })->firstOrFail();
        $creator = $timeline->user;

        $fan = $timeline->followers()->whereDoesntHave('subscribedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('users.id', '<>', $creator->id)->first();
        $this->assertTrue( $timeline->followers->contains($fan->id) );
        $this->assertFalse( $timeline->subscribers->contains($fan->id) );

        // ---

        $payload = [
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
            'type' => PostTypeEnum::SUBSCRIBER,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $post = Post::find($content->post->id);

        $filenames = [
            $this->faker->slug,
            $this->faker->slug,
        ];
        $files = [
            UploadedFile::fake()->image($filenames[0], 200, 200),
            UploadedFile::fake()->image($filenames[1], 200, 200),
        ];

        // attach 1st file
        $payload = [
            'mftype' => MediafileTypeEnum::POST,
            'mediafile' => $files[0],
            'resource_type' => 'posts',
            'resource_id' => $post->id,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);

        // attach 2nd file
        $payload = [
            'mftype' => MediafileTypeEnum::POST,
            'mediafile' => $files[1],
            'resource_type' => 'posts',
            'resource_id' => $post->id,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->data);

        $this->assertObjectNotHasAttribute('description', $content->data);
        $this->assertObjectNotHasAttribute('mediafiles', $content->data);
    }

    /**
     *  @group posts
     *  @group regression
     *  @group regression-base
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
     *  @group regression-base
     */
    public function test_nonfollower_can_view_image_of_free_post_on_my_timeline()
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
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        //$this->assertNotEquals($nonFan->id, $creator->id);
        //$this->assertFalse($timeline->followers->contains($nonFan->id));

        $post = $timeline->posts()->has('mediafiles', '>=', 1)
                         ->where('type', PostTypeEnum::FREE)
                         ->first();
        $mediafile = $post->mediafiles->shift();

        $response = $this->actingAs($nonFan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);

        $response = $this->actingAs($nonFan)->ajaxJSON('GET', route('mediafiles.show', $mediafile->id));
        $response->assertStatus(200);
    }

    /**
     *  @group posts
     *  @group regression
     *  @group regression-base
     */
    public function test_can_update_my_post()
    {
        $timeline = Timeline::whereHas('posts')->first();
        $creator = $timeline->user;
        $post = $timeline->posts()->where('price', 0)->first();

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
     *  @group regression-base
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
     *  @group regression-base
     */
    public function test_timeline_follower_can_like_then_unlike_post()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->firstOrFail();
        $creator = $timeline->user;
        $post = $timeline->posts->where('type', PostTypeEnum::FREE)->first();
        $fan = $timeline->followers[0];

        // remove any existing likes by fan...
        DB::table('likeables')
            ->where('liker_id', $fan->id)
            ->where('likeable_type', 'posts')
            ->where('likeable_id', $post->id)
            ->delete();

        // LIKE the post
        $payload = [
            'likeable_type' => 'posts',
            'likeable_id' => $post->id,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('likeables.update', $fan->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->likeable);
        $postR = $content->likeable;
        //$this->assertInstanceOf(Post::class, $postR);
        $this->assertEquals($post->id, $postR->id);

        $likeable = DB::table('likeables')
            ->where('liker_id', $fan->id)
            ->where('likeable_type', 'posts')
            ->where('likeable_id', $postR->id)
            ->first();
        $this->assertNotNull($likeable);
        $this->assertEquals($fan->id, $likeable->liker_id);
        $this->assertEquals('posts', $likeable->likeable_type);
        $this->assertEquals($postR->id, $likeable->likeable_id);

        // UNLIKE the post
        $payload = [
            'likeable_type' => 'posts',
            'likeable_id' => $post->id,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('DELETE', route('likeables.destroy', $fan->id), $payload);
        $response->assertStatus(200);

        $likeable = DB::table('likeables')
            ->where('liker_id', $fan->id)
            ->where('likeable_type', 'posts')
            ->where('likeable_id', $postR->id)
            ->first();
        $this->assertNull($likeable);
    }

    /**
     *  @group posts
     *  @group regression
     *  @group regression-base
     */
    public function test_timeline_nonfollower_can_like_post()
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

        // LIKE the post
        $payload = [
            'likeable_type' => 'posts',
            'likeable_id' => $post->id,
        ];
        $response = $this->actingAs($nonfan)->ajaxJSON('PUT', route('likeables.update', $nonfan->id), $payload);
        $response->assertStatus(200);
    }

    /**
     *  @group posts
     *  @group regression
     *  @group regression-base
     */
    public function test_timeline_owner_can_like_own_post()
    {
        $timeline = Timeline::has('followers', '>=', 1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->firstOrFail();
        $creator = $timeline->user;

        // LIKE the post (free)
        $post = $timeline->posts->where('type', PostTypeEnum::FREE)->first();
        $payload = [
            'likeable_type' => 'posts',
            'likeable_id' => $post->id,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PUT', route('likeables.update', $creator->id), $payload); 
        $response->assertStatus(200);

        // LIKE the post (priced)
        $post = $timeline->posts->where('type', PostTypeEnum::PRICED)->first();
        $payload = [
            'likeable_type' => 'posts',
            'likeable_id' => $post->id,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PUT', route('likeables.update', $creator->id), $payload); 
        $response->assertStatus(200);

        // LIKE the post (subcribe-only)
        $post = $timeline->posts->where('type', PostTypeEnum::SUBSCRIBER)->first();
        $payload = [
            'likeable_type' => 'posts',
            'likeable_id' => $post->id,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PUT', route('likeables.update', $creator->id), $payload); 
        $response->assertStatus(200);

    }

    /**
     *  @group posts
     *  @group regression
     *  @group regression-base
     *  @group erik
     */
    public function test_can_send_tip_on_post()
    {
        $this->assertTrue(Config::get('segpay.fake'), 'Your SEGPAY_FAKE .env variable is not set to true.');

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first(); // assume non-admin (%FIXME)
        $creator = $timeline->user;
        $post = $timeline->posts[0];
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
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('posts.tip', $post->id), $payload);
        $response->assertStatus(200);

        // ItemPurchased will update client with websocket event
        Event::assertDispatched(ItemTipped::class);
        Event::assertNotDispatched(TipFailed::class);

        $content = json_decode($response->content());

        // Tip was added to the database
        $this->assertDatabaseHas(Tip::getTableName(), [
            'sender_id'   => $fan->getKey(),
            'receiver_id' => $creator->getKey(),
            'tippable_id' => $post->getKey(),
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
     *  @group posts
     *  @group regression
     *  @group regression-base
     *  @group erik
     */
    // tests purchase itself, plus before and after access
    public function test_can_purchase_post()
    {
        $this->assertTrue(Config::get('segpay.fake'), 'Your SEGPAY_FAKE .env variable is not set to true.');

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
            'price' => 1000, // $10
            'currency' => 'USD'
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $post = Post::find($content->post->id);
        $this->assertNotNull($post);
        //$this->assertFalse( $fan->sharedposts->contains($post->id) ); // not yet shared w/ fan

        // Check access (before: should be denied)
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200); // can see post (locked icon, etc)...
        $content = json_decode($response->content());
        $this->assertObjectNotHasAttribute('description', $content->data); // ...can't see contents
        $this->assertObjectNotHasAttribute('mediafiles', $content->data);

        $events = Event::fake([
            ItemPurchased::class,
            PurchaseFailed::class
        ]);
        $account = $fan->financialAccounts()->where('type', AccountTypeEnum::IN)->with('resource')->first();
        $payload = [
            'account_id' => $account->getKey(),
            'amount'     => $post->price->getAmount(),
            'currency'   => $post->currency,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('posts.purchase', ['post' => $post->id]), $payload);
        $response->assertStatus(200);

        // ItemPurchased will update client with websocket event
        Event::assertDispatched(ItemPurchased::class);
        Event::assertNotDispatched(PurchaseFailed::class);

        $content = json_decode($response->content());

        // Check transactions ledger

        // Amount from Card
        $this->assertDatabaseHas('transactions', [
            'account_id' => $account->getKey(),
            'debit_amount' => $post->price->getAmount(),
        ], 'financial');

        // Amount from fan to creator
        $this->assertDatabaseHas('transactions', [
            'account_id' => $fan->getWalletAccount('segpay', 'USD')->getKey(),
            'debit_amount' => $post->price->getAmount(),
            'resource_id' => $post->getKey(),
        ], 'financial');

        // Amount to creator from fan
        $this->assertDatabaseHas('transactions', [
            'account_id' => $creator->getEarningsAccount('segpay', 'USD')->getKey(),
            'credit_amount' => $post->price->getAmount(),
            'resource_id' => $post->getKey(),
        ], 'financial');

        // Fan has access
        $post->refresh();
        $this->assertTrue($post->sharees->contains($fan->id));

        // Check access (after: should be allowed)
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertObjectHasAttribute('description', $content->data); // can see contents
        $this->assertObjectHasAttribute('mediafiles', $content->data);
    }


    /**
     * Refers to editing the content of a post that other have purchased. To avoid bait and switch by creators
     *
     *  @group posts
     *  @group regression
     *  @group regression-base
     *  @group erik
     */
    public function test_owner_can_not_edit_content_of_a_priced_post_that_others_have_purchased()
    {
        $this->assertTrue(Config::get('segpay.fake'), 'Your SEGPAY_FAKE .env variable is not set to true.');

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

        $events = Event::fake([
            ItemPurchased::class,
            PurchaseFailed::class
        ]);
        $account = $fan->financialAccounts()->where('type', AccountTypeEnum::IN)->with('resource')->first();
        $payload = [
            'account_id' => $account->getKey(),
            'amount'     => $post->price->getAmount(),
            'currency'   => $post->currency,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('posts.purchase', ['post' => $post->id]), $payload);
        $response->assertStatus(200);

        // ItemPurchased will update client with websocket event
        Event::assertDispatched(ItemPurchased::class);
        Event::assertNotDispatched(PurchaseFailed::class);

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
     *  @group regression-base
     *  @group erik
     */
    // priced: one-time-purchaseable, as opposed to subscribeable
    public function test_owner_can_not_delete_a_priced_post_that_others_have_purchased()
    {
        $this->assertTrue(Config::get('segpay.fake'), 'Your SEGPAY_FAKE .env variable is not set to true.');

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

        $events = Event::fake([
            ItemPurchased::class,
            PurchaseFailed::class
        ]);
        $account = $fan->financialAccounts()->where('type', AccountTypeEnum::IN)->with('resource')->first();
        $payload = [
            'account_id' => $account->getKey(),
            'amount'     => $post->price->getAmount(),
            'currency'   => $post->currency,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('posts.purchase', [ 'post' => $post->id ]), $payload);
        $response->assertStatus(200);

        // ItemPurchased will update client with websocket event
        Event::assertDispatched(ItemPurchased::class);
        Event::assertNotDispatched(PurchaseFailed::class);

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
        //$this->seed(TestDatabaseSeeder::class);
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

