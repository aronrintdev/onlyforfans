<?php
namespace Tests\Unit;

use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;

use Carbon\Carbon;
use Tests\TestCase;

use App\Apis\Sendgrid\Api as SendgridApi;
use App\Notifications\TipReceived;
use App\Models\Tip;

// %NOTE: these tests are meant for manual inspection, not automation

// Send mail via SendGrid (default) in Sandbox Mode
// $ DEBUG_BYPASS_SENDGRID_MAIL_NOTIFY=false DEBUG_ENABLE_SENDGRID_SANDBOX_MODE=true DEBUG_OVERRIDE_TO_EMAIL_FOR_SENDGRID="peter@peltronic.com" php artisan test --group="notify-via-sendgrid-unit"
// $ DEBUG_BYPASS_SENDGRID_MAIL_NOTIFY=false DEBUG_ENABLE_SENDGRID_SANDBOX_MODE=true DEBUG_OVERRIDE_TO_EMAIL_FOR_SENDGRID="peter@peltronic.com" php artisan test --group="here0721"

class SendgridApiTest extends TestCase
{
    /**
     * @group sendgrid-api-unit
     * @group NO-regression
     * @group OFF-here0719
     */
    public function test_should_send_email_direct_via_sendgrid_wrapper_api_tip_received()
    {
        $response = SendgridApi::send('new-tip-received', [
            //'subject' => 'Subject Override Ex',
            'to' => [
                'email' => 'peter+test1@peltronic.com', 
                'name' => 'Peter Test1',
            ],
            'dtdata' => [
                'sender_name' => 'Joe Tipsender',
                'amount' => '$33.10',
                'home_url' => url('/'),
                'referral_url' => url('/referrals'),
                'privacy_url' => url('/privacy'),
                'manage_preferences_url' => url( route('users.showSettings', $notifiable->username) ),
                'unsubscribe_url' => url( route('users.showSettings', $notifiable->username) ),
            ],
        ]);
        $isSandbox = env('DEBUG_ENABLE_SENDGRID_SANDBOX_MODE', false);
        $this->assertEquals( $isSandbox?200:202, $response->statusCode() );
    }

    /**
     * @group sendgrid-api-unit
     * @group here0721
     * @group NO-regression
     */
    public function test_should_notify_tip_received()
    {

        $tip = Tip::where('tippable_type', 'posts')->first(); // the 'tip'
        $post = $tip->tippable; // the 'tippable'
        $sender = $tip->sender;
        $receiver = $tip->receiver;
        $result = Notification::send( $receiver, new TipReceived($post, $sender, ['amount'=>$tip->amount]) );
        $this->assertNull($result);

        dump('info', 
            'tip:', [
                'sender' => $sender->name??'-',
                'receiver' => $receiver->name??'-',
                'amount' => $tip->amount->getAmount()??'-',
            ],
            ['sender' => $sender->name??'-'],
            ['post-tippable', $post->slug??'-'],
        );
    }

    /**
     * @group sendgrid-api-unit
     * @group NO-regression
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

