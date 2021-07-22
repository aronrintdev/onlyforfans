<?php
namespace Tests\Unit;

use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;

use Carbon\Carbon;
use Money\Money;
use Tests\TestCase;

use App\Notificaitons\CampaignGoalReached;
use App\Notificaitons\CommentReceived;
use App\Notificaitons\EmailVerified;
use App\Notificaitons\IdentityVerificationRejected;
use App\Notificaitons\IdentityVerificationRequestSent;
use App\Notificaitons\IdentityVerificationVerified;
use App\Notificaitons\MessageReceived;
use App\Notificaitons\NewCampaignContributionReceived;
use App\Notificaitons\NewReferralReceived;
use App\Notificaitons\NewSubPaymentReceived;
use App\Notificaitons\NotifyTraits;
use App\Notificaitons\PasswordChanged;
use App\Notificaitons\PasswordReset;
use App\Notificaitons\PostTipped;
use App\Notificaitons\ResourceLiked;
use App\Notificaitons\ResourcePurchased;
use App\Notificaitons\SubRenewalPaymentReceived;
use App\Notificaitons\SubRenewalPaymentReceivedReturningSubscriber;
use App\Notificaitons\TimelineFollowed;
use App\Notificaitons\TimelineSubscribed;
use App\Notificaitons\TipReceived;
use App\Notificaitons\VaultfileShareSent;

use App\Models\Campaign;
use App\Models\Chatmessage;
use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use App\Models\Timeline;
use App\Models\Subscription;
use App\Models\Tip;
use App\Models\Verifyrequest;

use App\Enums\VerifyStatusTypeEnum;

// Send mail via laravel native, not SendGrid:
// $ DEBUG_BYPASS_SENDGRID_MAIL_NOTIFY=true php artisan test --group="lib-notification-unit"

/*
[/] password-reset
[/] password-changed-confirmation
[/] id-verification-pending
[/] id-verification-approved
[/] id-verification-rejected
[/] email-verified

[/] new-subscription-payment-received
[/] subscription-renewal-payment-received
[/] subscription-payment-received-from-returning-subscriber
[o] new-campaign-contribution-received
[o] campaign-goal-reached
[o] new-referral-received
[/] new-message-received
[/] new-tip-received
[/] new-comment-received
 */

class NotificationTest extends TestCase
{
    use WithFaker;

    /**
     * @group lib-notification-unit
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_new_message_received()
    {
        Notification::fake();
        $receiver = User::has('chatthreads')->firstOrFail();

        $chatthread = $receiver->chatthreads->first();
        $this->assertNotNull($chatthread);
        $this->assertNotNull($chatthread->id);

        $chatmessage = $chatthreads->chatmessages->first();
        $this->assertNotNull($chatmessage);
        $this->assertNotNull($chatmessage->id);

        $result = Notification::send( $receiver, new MessageReceived($chatmessage, $sender );
        Notification::assertSentTo( [$receiver], MessageReceived::class );
    }

    /**
     * @group lib-notification-unit
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_new_referral_received()
    {
        $this->assertTrue(false, 'to-be-implemented')
            /*
        Notification::fake();
        $campaign = Campaign::first();
        $actor = $timeline->followers->first(); // fan
        $receiver = $campaign->creator; // creator
        $amount = Money::USD( $this->faker->numberBetween(1, 20) * 500 ); // %FIXME: should be associated with campaign?
        $result = Notification::send( $receiver, new NewCampaignContributionReceived($campaign, $actor, ['amount'=>$amount]) );
        Notification::assertSentTo( [$receiver], NewCampaignContributionReceived::class );
             */
    }

    /**
     * @group lib-notification-unit
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_new_campaign_goal_reached()
    {
        $this->assertTrue(false, 'to-be-implemented')
            /*
        Notification::fake();
        $campaign = Campaign::first();
        $actor = $timeline->followers->first(); // fan
        $receiver = $campaign->creator; // creator
        $amount = Money::USD( $this->faker->numberBetween(1, 20) * 500 ); // %FIXME: should be associated with campaign?
        $result = Notification::send( $receiver, new NewCampaignContributionReceived($campaign, $actor, ['amount'=>$amount]) );
        Notification::assertSentTo( [$receiver], NewCampaignContributionReceived::class );
             */
    }

    /**
     * @group lib-notification-unit
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_new_campaign_contribution_received()
    {
        $this->assertTrue(false, 'to-be-implemented')
            /*
        Notification::fake();
        $campaign = Campaign::first();
        $actor = $timeline->followers->first(); // fan
        $receiver = $campaign->creator; // creator
        $amount = Money::USD( $this->faker->numberBetween(1, 20) * 500 ); // %FIXME: should be associated with campaign?
        $result = Notification::send( $receiver, new NewCampaignContributionReceived($campaign, $actor, ['amount'=>$amount]) );
        Notification::assertSentTo( [$receiver], NewCampaignContributionReceived::class );
             */
    }

    /**
     * @group lib-notification-unit
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_sub_renewal_payment_received()
    {
        Notification::fake();
        $subscribable = Timeline::has('followers', '>=', 1)->first(); // timeline
        $actor = $timeline->followers->first(); // fan
        $receiver = $timeline->user; // creator
        $amount = Money::USD( $this->faker->numberBetween(1, 20) * 500 );
        $result = Notification::send( $receiver, new SubRenewalPaymentReceived($subscribable, $actor, ['amount'=>$amount]) );
        Notification::assertSentTo( [$receiver], SubRenewalPaymentReceived::class );
    }

    /**
     * @group lib-notification-unit
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_sub_renewal_payment_received_returning_subscriber()
    {
        Notification::fake();
        $subscribable = Timeline::has('followers', '>=', 1)->first(); // timeline
        $actor = $timeline->followers->first(); // fan
        $receiver = $timeline->user; // creator
        $amount = Money::USD( $this->faker->numberBetween(1, 20) * 500 );
        $result = Notification::send( $receiver, new SubRenewalPaymentReceivedReturningSubscriber($subscribable, $actor, ['amount'=>$amount]) );
        Notification::assertSentTo( [$receiver], SubRenewalPaymentReceivedReturningSubscriberSubRenewalPaymentReceived::class );
    }

    /**
     * @group lib-notification-unit
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_new_sub_payment_received()
    {
        Notification::fake();
        $subscribable = Timeline::has('followers', '>=', 1)->first(); // timeline
        $actor = $timeline->followers->first(); // fan
        $receiver = $timeline->user; // creator
        $amount = Money::USD( $this->faker->numberBetween(1, 20) * 500 );
        $result = Notification::send( $receiver, new NewSubPaymentReceived($subscribable, $actor, ['amount'=>$amount]) );
        Notification::assertSentTo( [$receiver], NewSubPaymentReceived::class );
    }

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
        Notification::assertSentTo( [$receiver], TipReceived::class );
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
        Notification::assertSentTo( [$receiver], CommentReceived::class );
    }

    /**
     * @group lib-notification-unit
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_email_verified()
    {
        Notification::fake();
        $user = User::first();
        $result = Notification::send( $user, new EmailVerified($user) );
        Notification::assertSentTo( [$user], EmailVerified::class );
    }


    /**
     * @group lib-notification-unit
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_password_changed()
    {
        Notification::fake();
        $user = User::first();
        $result = Notification::send( $user, new PasswordChanged($user) );
        Notification::assertSentTo( [$user], PasswordChanged::class );
    }

    /**
     * @group lib-notification-unit
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_password_reset()
    {
        Notification::fake();
        $user = User::first();
        $result = Notification::send( $user, new PasswordReset($user) );
        Notification::assertSentTo( [$user], PasswordReset::class );
    }

    /**
     * @group lib-notification-unit
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_id_verification_pending()
    {
        Notification::fake();

        $user = User::first();

        $vr = Verifyrequest::create([
            'service_guid' => $this->faker->uuid,
            'vservice' => 'fake-service',
            'vstatus' => VerifyStatusTypeEnum::PENDING,
            'requester_id' => $user->id,
            'last_checked_at' => '2021-07-17 01:48:49',
        ]);

        Notification::send( $user, new IdentityVerificationPending($vr, $user));

        Notification::assertSentTo( [$user], IdentityVerificationPending::class );
    }

    /**
     * @group lib-notification-unit
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_id_verification_verified()
    {
        Notification::fake();

        $user = User::first();

        $vr = Verifyrequest::create([
            'service_guid' => $this->faker->uuid,
            'vservice' => 'fake-service',
            'vstatus' => VerifyStatusTypeEnum::VERIFIED,
            'requester_id' => $user->id,
            'last_checked_at' => '2021-07-17 01:48:49',
        ]);

        Notification::send( $user, new IdentityVerificationVerified($vr, $user));

        Notification::assertSentTo( [$user], IdentityVerificationVerified::class );
    }

    /**
     * @group lib-notification-unit
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_id_verification_rejected()
    {
        Notification::fake();

        $user = User::first();

        $vr = Verifyrequest::create([
            'service_guid' => $this->faker->uuid,
            'vservice' => 'fake-service',
            'vstatus' => VerifyStatusTypeEnum::REJECTED,
            'requester_id' => $user->id,
            'last_checked_at' => '2021-07-17 01:48:49',
        ]);

        Notification::send( $user, new IdentityVerificationRejected($vr, $user));

        Notification::assertSentTo( [$user], IdentityVerificationRejected::class );
    }

}

