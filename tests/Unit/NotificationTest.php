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
use App\Models\Tip;

// Send mail via laravel native, not SendGrid:
// $ DEBUG_BYPASS_SENDGRID_MAIL_NOTIFY=true php artisan test --group="lib-notification-unit"

class NotificationTest extends TestCase
{
    /**
     * @group lib-notification-unit
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_tip_received()
    {
        Notification::fake(); // this should bypass sending mail, even to SendGrid (?)

        $tip = Tip::where('tippable_type', 'posts')->first(); // the 'tip'
        $post = $tip->tippable; // the 'tippable'
        $sender = $tip->sender;
        $receiver = $tip->receiver;

        $result = Notification::send( $receiver, new TipReceived($post, $sender, ['amount'=>$tip->amount]) );

        Notification::assertSentTo(
            [$receiver], TipReceived::class
        );
    }

    /**
     * @group lib-notification-unit
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_comment_received()
    {
        Notification::fake();

        $comment = Comment::first();
        $sender = $comment->user;
        $receiver = $comment->post->timeline->user;

        Notification::send( $receiver, new CommentReceived($comment, $sender));

        Notification::assertSentTo(
            [$receiver], CommentReceived::class
        );
    }

}
