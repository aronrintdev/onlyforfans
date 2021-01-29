<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;

use Database\Seeders\TestDatabaseSeeder;
use App\Enums\PostTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\User;
use App\Post;

class PostTest extends TestCase
{
    //use RefreshDatabase, WithFaker;
    //use WithFaker;
    use DatabaseTransactions, WithFaker;

    /**
     *  @group postdev
     */
    // %TODO: filters, timelines (see posts I follow), etc
    public function test_can_index_posts()
    {
        //$this->seed(\Database\Seeders\TestDatabaseSeeder::class);
        $creator = User::has('posts', '>=', 1)->first(); // assume non-admin (%FIXME)

        $response = $this->actingAs($creator)->ajaxJSON('GET', route('posts.index'));
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->posts);
        $postsR = collect($content->posts);
        //dd($postsR, $postsR[0]);
        $this->assertGreaterThan(0, $postsR->count());
        $this->assertNotNull($postsR[0]->description);
        $this->assertNotNull($postsR[0]->timeline_id);
        $this->assertEquals($postsR[0]->timeline_id, $creator->timeline->id);
    }

    /**
     *  @group postdev
     */
    public function test_can_show_my_post()
    {
        $creator = User::has('posts', '>=', 1)->first(); // assume non-admin (%FIXME)
        $post = $creator->posts[0];

        $response = $this->actingAs($creator)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $this->assertNotNull($postR->description);
        $this->assertNotNull($postR->timeline_id);
        $this->assertEquals($postR->timeline_id, $creator->timeline->id);
    }

    /**
     *  @group postdev
     */
    public function test_can_show_followed_timelines_post()
    {
        $creator = User::has('posts', '>=', 1)
            ->has('followers', '>=', 1)
            ->first(); // assume non-admin (%FIXME)
        $post = $creator->posts[0];
        $fan = $creator->followers[0];

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $this->assertNotNull($postR->timeline_id);
        $this->assertEquals($postR->timeline_id, $creator->timeline->id);

        // %TODO: can not show unfollowed timeline's post
    }

    /**
     *  @group postdev
     */
    public function test_can_store_post()
    {
        $creator = User::has('posts', '>=', 1)
            ->first(); // assume non-admin (%FIXME)

        $payload = [
            'timeline_id' => $creator->timeline->id,
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $this->assertNotNull($content->description);
        $this->assertEquals($payload['description'], $content->description);
    }

    /**
     *  @group postdev
     */
    public function test_can_update_post()
    {
        $creator = User::has('posts', '>=', 1)
            ->first(); // assume non-admin (%FIXME)
        $post = $creator->posts[0];

        $payload = [
            'description' => 'updated text',
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('posts.update', $post->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $this->assertNotNull($content->description);
        $this->assertEquals($payload['description'], $content->description);
    }

    /**
     *  @group postdev
     */
    public function test_can_destroy_post()
    {
        $creator = User::has('posts', '>=', 2)
            ->first(); // assume non-admin (%FIXME)
        $post = $creator->posts[0];
        $response = $this->actingAs($creator)->ajaxJSON('DELETE', route('posts.destroy', $post->id));
        $response->assertStatus(200);

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(404);
    }

    /**
     *  @group postdev
     */
    public function test_can_like_post()
    {
    //Route::put('/likeables/{likee}', ['as'=>'likeables.update', 'uses' => 'LikeablesController@update']); // %FIXME: refactor to make consistent
        $creator = User::has('posts', '>=', 1)
            ->has('followers', '>=', 1)
            ->first(); // assume non-admin (%FIXME)
        $post = $creator->posts[0];
        $fan = $creator->followers[0];

        $payload = [
            'description' => 'updated text',
        ];
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('likeables.update', $fan->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $this->assertNotNull($postR->timeline_id);
        $this->assertEquals($postR->timeline_id, $creator->timeline->id);
    }

    /**
     *  @group postdev
     */
    public function test_can_comment_on_post()
    {
    }

    /**
     *  @group postdev
     */
    public function test_can_share_post()
    {
    }

    /**
     *  @group postdev
     */
    public function test_can_tip_post()
    {
    }

    /**
     *  @group postdev
     */
    public function test_can_purchase_post()
    {
    }

    /**
     *  @group postdev
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

