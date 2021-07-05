<?php
namespace Tests\Feature;

use DB;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification as NotificationFacade;
//use Illuminate\Support\Facades\Mail;
//use Illuminate\Notifications\Messages\MailMessage;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

use App\Notifications\ResourceLiked as NotifyResourceLiked;
use App\Notifications\CommentReceived as NotifyCommentReceived;
use App\Models\Post;
use App\Models\User;
use App\Models\Timeline;
use App\Models\Notification;
use App\Enums\PostTypeEnum;

class RestNotificationsTest extends TestCase
{
    //use RefreshDatabase;
    use WithFaker;

    /**
     *  @group notifications
     *  @group regression
     *  @group regression-base
     */
    public function test_owner_can_list_notifications()
    {
        $owner = User::has('notifications', '>=', 1)
            ->firstOrFail();

        $response = $this->actingAs($owner)->ajaxJSON('GET', route('notifications.index'), [
            //'type' => 'ResourceLiked',
        ]);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);
        $this->assertEquals(1, $content->meta->current_page);

        $this->assertNotNull($content->data);
        $this->assertGreaterThan(0, count($content->data));
        $this->assertObjectHasAttribute('type', $content->data[0]);
        $this->assertObjectHasAttribute('notifiable_type', $content->data[0]);
        $this->assertObjectHasAttribute('notifiable_id', $content->data[0]);

        // All notifications returned are owned 
        $ownedCount = collect($content->data)->reduce( function($acc, $item) use(&$owner) {
            switch ( $item->notifiable_type ) {
            case 'users':
                $notifiable = User::findOrFail($item->notifiable_id);
                break;
            default:
                throw new Exception('Unknown notifiable_type: '.$item->notifiable_type);
            }
            if ( $notifiable->id === $owner->id ) {
                $acc += 1;
            }
            return $acc;
        }, 0);
        $this->assertEquals(count($content->data), $ownedCount); 
    }

    /**
     *  @group notifications
     *  @group regression
     *  @group regression-base
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
        $creator->refresh();

        NotificationFacade::assertSentTo($creator, NotifyResourceLiked::class, function ($notification, $channels) {
            return in_array('mail', $channels);
        });
    }

    /**
     *  @group notifications
     *  @group regression
     *  @group regression-base
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
     *  @group regression
     *  @group regression-base
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
        $creator->refresh();

        NotificationFacade::assertNotSentTo($creator, NotifyResourceLiked::class, function ($notification, $channels) {
            return in_array('mail', $channels);
        });
    }


    /**
     *  @group notifications
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
        //$this->seed(TestDatabaseSeeder::class);
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}
