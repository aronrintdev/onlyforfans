<?php
namespace Tests\Feature;

use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification as NotificationFacade;
//use Illuminate\Support\Facades\Mail;
//use Illuminate\Notifications\Messages\MailMessage;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

use App\Notifications\ResourceLiked as NotifyResourceLiked;
use App\Notifications\CommentReceived as NotifyCommentReceived;
use App\Models\User;
use App\Models\Timeline;
use App\Models\Notification;
use App\Enums\PostTypeEnum;

class RestNotificationsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group notifications
     *  @group here0429
     *  @group regression
     */
    public function test_sends_email_on_like_when_enabled_in_settings()
    {
        NotificationFacade::fake();

        $timeline = Timeline::has('followers','>=',1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->firstOrFail();
        $creator = $timeline->user;
        $fan = $timeline->followers[0];
        $post = $timeline->posts->where('type', PostTypeEnum::FREE)->first();

        // Enable email global setting
        $payload = [
            'global' => [ 'enabled' => ['email'] ],
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('users.enableSetting', [$creator->id, 'notifications']), $payload);
        $response->assertStatus(200);

        // Enable setting
        $payload = [
            'posts' => [ 'new_like' => ['email'] ],
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('users.enableSetting', [$creator->id, 'notifications']), $payload);
        $response->assertStatus(200);

        //$content = json_decode($response->content());

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
        $creator->refresh();

        NotificationFacade::assertSentTo($creator, NotifyResourceLiked::class, function ($notification, $channels) {
            return in_array('mail', $channels);
        });
    }

    /**
     *  @group notifications
     *  @group here0506
     *  @group regression
     */
    public function test_sends_email_on_comment_when_enabled_in_settings()
    {
        NotificationFacade::fake();

        $timeline = Timeline::has('followers','>=',1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->firstOrFail();
        $creator = $timeline->user;
        $fan = $timeline->followers[0];
        $post = $timeline->posts->where('type', PostTypeEnum::FREE)->first();

        // Enable email global setting
        $payload = [
            'global' => [ 'enabled' => ['email'] ],
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('users.enableSetting', [$creator->id, 'notifications']), $payload);
        $response->assertStatus(200);

        // Enable setting
        $payload = [
            'posts' => [ 'new_comment' => ['email'] ],
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('users.enableSetting', [$creator->id, 'notifications']), $payload);
        $response->assertStatus(200);

        //$content = json_decode($response->content());

        // Comment on the post
        $payload = [
            'post_id' => $post->id,
            'user_id' => $fan->id,
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('POST', route('comments.store'), $payload);
        $response->assertStatus(201);
        $creator->refresh();

        NotificationFacade::assertSentTo($creator, NotifyCommentReceived::class, function ($notification, $channels) {
            return in_array('mail', $channels);
        });
    }

    /**
     *  @group notifications
     *  @group here0504
     *  @group regression
     */
    public function test_does_not_send_email_on_like_when_disabled_in_settings()
    {
        NotificationFacade::fake();

        $timeline = Timeline::has('followers','>=',1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->firstOrFail();
        $creator = $timeline->user;
        $fan = $timeline->followers[0];
        $post = $timeline->posts->where('type', PostTypeEnum::FREE)->first();


        // Enable setting
        $payload = [
            'posts' => [ 'new_like' => ['email'] ],
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('users.enableSetting', [$creator->id, 'notifications']), $payload);
        $response->assertStatus(200);

        // Disable setting
        $payload = [
            'posts' => [ 'new_like' => ['email'] ],
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('users.disableSetting', [$creator->id, 'notifications']), $payload);
        $response->assertStatus(200);

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
        $creator->refresh();

        NotificationFacade::assertNotSentTo($creator, NotifyResourceLiked::class, function ($notification, $channels) {
            return in_array('mail', $channels);
        });
    }


    /**
     *  @group notifications
     *  @group here0429
     *  @group here0506
     *  @group OFF-regression
     */
    /*
    public function test_does_not_send_email_on_comment_when_disabled_in_settings()
    {
        NotificationFacade::fake();

        $timeline = Timeline::has('followers','>=',1)
            ->whereHas('posts', function($q1) {
                $q1->where('type', PostTypeEnum::FREE);
            })->firstOrFail();
        $creator = $timeline->user;
        $fan = $timeline->followers[0];
        $post = $timeline->posts->where('type', PostTypeEnum::FREE)->first();

        // Enable email global setting
        $payload = [
            'global' => [ 'enabled' => ['email'] ],
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('users.enableSetting', [$creator->id, 'notifications']), $payload);
        $response->assertStatus(200);

        // Enable setting
        $payload = [
            'posts' => [ 'new_comment' => ['email'] ],
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('users.enableSetting', [$creator->id, 'notifications']), $payload);
        $response->assertStatus(200);

        // Disable setting
        $payload = [
            'posts' => [ 'new_comment' => ['email'] ],
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('users.disableSetting', [$creator->id, 'notifications']), $payload);
        $response->assertStatus(200);

        // Comment on the post
        $payload = [
            'post_id' => $post->id,
            'user_id' => $fan->id,
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('POST', route('comments.store'), $payload);
        $response->assertStatus(201);
        $creator->refresh();

        NotificationFacade::assertNotSentTo($creator, NotifyCommentReceived::class, function ($notification, $channels) {
            return in_array('mail', $channels);
        });
    }
     */

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
