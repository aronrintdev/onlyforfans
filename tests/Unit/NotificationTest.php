<?php
namespace Tests\Unit;

use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;

use Carbon\Carbon;
use Tests\TestCase;

use App\Notifications\CommentReceived;
use App\Models\Comment;
use App\Models\User;

class NotificationTest extends TestCase
{
    /**
     * @group lib-notification
     * @group here0719
     // do not add to regression tests!
     */
    public function test_should_basic()
    {

        $comment = Comment::first();
        $users = User::take(1)->get();
        Notification::send($users, new CommentReceived($comment, $comment->user));
        $this->assertTrue(true);

        /*
        $api = IdMeritApi::create();
        $response = $api->issueToken();
        $this->assertEquals( 200, $response->status() );
        $json = $response->json();
        //dd( $json );
        $this->assertArrayHasKey('access_token', $json);
        $this->assertArrayHasKey('token_type', $json);
        $this->assertArrayHasKey('expires_in', $json);
        //dd( $response );
         */
    }

}

