<?php
namespace Tests\Unit;

use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;

use Carbon\Carbon;
use Tests\TestCase;

use App\Notifications\TipReceived;
use App\Notifications\CommentReceived;
use App\Models\Comment;
use App\Models\User;
use App\Models\Post;

class NotificationTest extends TestCase
{
    /**
     * @group lib-notification
     * @group OFF-here0719
     // do not add to regression tests!
     */
    public function test_should_notify_tip_received()
    {
        //Notification::fake();

        $post = Post::first(); // the 'tippable'
        $sender = User::first();
        Notification::send( collect($sender), new TipReceived($post, $post->timeline->user) );
        //$this->assertTrue(true);

        Notification::assertSentTo(
            [$post->timeline->user], TipReceived::class
        );

    }
    /**
     * @group lib-notification
     * @group here0719
     // do not add to regression tests!
     */
    public function test_should_notify_comment_received()
    {
        //Notification::fake();

        $comment = Comment::first();
        $sender = $comment->user;
        $receiver = $comment->post->timeline->user;
//dd($sender);

        dump('info', 'receiver: '.$receiver->name, 'sender: '.$sender->name, 'comment: '.$comment->id);

        Notification::send( $receiver, new CommentReceived($comment, $sender));

        $this->assertTrue(true);

        /*
        Notification::assertSentTo(
            [$receiver], CommentReceived::class
        );
         */

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

