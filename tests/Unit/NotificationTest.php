<?php
namespace Tests\Unit;

use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;

use Carbon\Carbon;
use Money\Money;
use Tests\TestCase;

use App\Notifications\CampaignGoalReached;
use App\Notifications\CommentReceived;
use App\Notifications\LikeReceived;
use App\Notifications\TagReceived;
use App\Notifications\VerifyEmail;
use App\Notifications\EmailVerified;
use App\Notifications\IdentityVerificationRejected;
use App\Notifications\IdentityVerificationRequestSent;
use App\Notifications\IdentityVerificationVerified;
use App\Notifications\InviteStaffMember;
use App\Notifications\InviteStaffManager;
use App\Notifications\MessageReceived;
use App\Notifications\NewCampaignContributionReceived;
use App\Notifications\NewReferralReceived;
use App\Notifications\NewSubPaymentReceived;
use App\Notifications\NotifyTraits;
use App\Notifications\PasswordChanged;
use App\Notifications\PasswordReset;
use App\Notifications\PostTipped;
use App\Notifications\ResourceLiked;
use App\Notifications\ResourcePurchased;
use App\Notifications\SubRenewalPaymentReceived;
use App\Notifications\SubRenewalPaymentReceivedReturningSubscriber;
use App\Notifications\TimelineFollowed;
use App\Notifications\TimelineSubscribed;
use App\Notifications\TipReceived;
use App\Notifications\VaultfileShareSent;

use App\Models\Campaign;
use App\Models\Chatmessage;
use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use App\Models\Timeline;
use App\Models\Subscription;
use App\Models\Tip;
use App\Models\Verifyrequest;

use App\Enums\CampaignTypeEnum;
use App\Enums\PostTypeEnum;
use App\Enums\VerifyStatusTypeEnum;

// Send mail via laravel native, not SendGrid:
// $ DEBUG_BYPASS_SENDGRID_MAIL_NOTIFY=true php artisan test --group="lib-notification-unit-fake"

class NotificationTest extends TestCase
{
    use WithFaker;

    // ----- %%% Messages -----

    /**
     * @group lib-notification-unit-fake
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

        $chatmessage = $chatthread->chatmessages->first();
        $this->assertNotNull($chatmessage);
        $this->assertNotNull($chatmessage->id);

        $sender = $chatmessage->sender;

        $result = Notification::send( $receiver, new MessageReceived($chatmessage, $sender) );
        Notification::assertSentTo( [$receiver], MessageReceived::class );
    }

    // ----- %%% Referrals -----

    /**
     * @group lib-notification-unit-fake
     * @group OFF-regression
     * @group OFF-regression-unit
     */
    public function test_should_notify_new_referral_received()
    {
        $this->assertTrue(false, 'to-be-implemented');
        /*
        Notification::fake();
        $result = Notification::send( $receiver, new NewReferralReceived($referral, $sender) );
        Notification::assertSentTo( [$receiver], NewReferralReceived::class );
         */
    }

    // ----- %%% Promotions -----

    /**
     * @group lib-notification-unit-fake
     * @group OFF-regression
     * @group OFF-regression-unit
     * @group OFF-here0913
     */
    public function test_should_notify_new_promotion_campaign_started()
    {
        Notification::fake();
        $timeline = Timeline::has('followers', '>=', 1)->first(); // subscribable
        $creator = $timeline->user;

        $userSettings = $creator->settings;

        // Set a subscrition price
        $subPriceInDollars = $this->faker->numberBetween(1, 20) * 5;
        $subPriceInCents = $subPriceInDollars * 100;
        $result = $userSettings->setValues('subscriptions', [ 'price_per_1_months' => $subPriceInDollars ]); // %FIXME

        $timeline->refresh();
        $subPrice = $timeline->getBaseSubPriceInCents(); // actual, read from model
        $this->assertEquals($subPriceInCents, $subPrice);

        // create a new promotion campaign
        Campaign::deactivateAll($creator);

        $subscriberCount = 2;
        $message = "Hey come subscribe you won't be disappointed!";
        $attrs = [ 
            'type' => CampaignTypeEnum::DISCOUNT,
            'has_new' => true, 
            'has_expired' => true, 
            'offer_days' => 7, 
            'discount_percent' => 10, 
            'trial_days' => 7, 
        ];
        $attrs['creator_id'] = $creator->id;
        $attrs['subscriber_count'] = $subscriberCount;
        $attrs['message'] = $message;

        $campaign = Campaign::create($attrs);

        $sender = $timeline->followers->first(); // fan
        $receiver = $campaign->creator; // creator

        $amount = CastsMoney::USD($subPrice);
        $result = Notification::send( $receiver, new NewCampaignContributionReceived($campaign, $sender, ['amount'=>$amount]) );
        Notification::assertSentTo( [$receiver], NewCampaignContributionReceived::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group OFF-regression
     * @group OFF-regression-unit
     */
    public function test_should_notify_new_campaign_goal_reached()
    {
        $this->assertTrue(false, 'to-be-implemented');
            /*
        Notification::fake();
        $campaign = Campaign::first();
        $sender = $timeline->followers->first(); // fan
        $receiver = $campaign->creator; // creator
        $amount = Money::USD( $this->faker->numberBetween(1, 20) * 500 ); // %FIXME: should be associated with campaign?
        $result = Notification::send( $receiver, new NewCampaignContributionReceived($campaign, $sender, ['amount'=>$amount]) );
        Notification::assertSentTo( [$receiver], NewCampaignContributionReceived::class );
             */
    }

    /**
     * @group lib-notification-unit-fake
     * @group OFF-regression
     * @group OFF-regression-unit
     */
    public function test_should_notify_new_campaign_contribution_received()
    {
        $this->assertTrue(false, 'to-be-implemented');
            /*
        Notification::fake();
        $campaign = Campaign::first();
        $sender = $timeline->followers->first(); // fan
        $receiver = $campaign->creator; // creator
        $amount = Money::USD( $this->faker->numberBetween(1, 20) * 500 ); // %FIXME: should be associated with campaign?
        $result = Notification::send( $receiver, new NewCampaignContributionReceived($campaign, $sender, ['amount'=>$amount]) );
        Notification::assertSentTo( [$receiver], NewCampaignContributionReceived::class );
             */
    }

    // ----- %%% Transactions -----

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_sub_renewal_payment_received()
    {
        Notification::fake();
        $timeline = Timeline::has('followers', '>=', 1)->first(); // subscribable
        $sender = $timeline->followers->first(); // fan
        $receiver = $timeline->user; // creator
        $amount = Money::USD( $this->faker->numberBetween(1, 20) * 500 );
        $result = Notification::send( $receiver, new SubRenewalPaymentReceived($timeline, $sender, ['amount'=>$amount]) );
        Notification::assertSentTo( [$receiver], SubRenewalPaymentReceived::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_sub_renewal_payment_received_returning_subscriber()
    {
        Notification::fake();
        $timeline = Timeline::has('followers', '>=', 1)->first(); // subscribable
        $sender = $timeline->followers->first(); // fan
        $receiver = $timeline->user; // creator
        $amount = Money::USD( $this->faker->numberBetween(1, 20) * 500 );
        $result = Notification::send( $receiver, new SubRenewalPaymentReceivedReturningSubscriber($timeline, $sender, ['amount'=>$amount]) );
        Notification::assertSentTo( [$receiver], SubRenewalPaymentReceivedReturningSubscriber::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_new_sub_payment_received()
    {
        Notification::fake();
        $timeline = Timeline::has('followers', '>=', 1)->first(); // subscribable
        $sender = $timeline->followers->first(); // fan
        $receiver = $timeline->user; // creator
        $amount = Money::USD( $this->faker->numberBetween(1, 20) * 500 );
        $result = Notification::send( $receiver, new NewSubPaymentReceived($timeline, $sender, ['amount'=>$amount]) );
        Notification::assertSentTo( [$receiver], NewSubPaymentReceived::class );
    }

    /**
     * @group lib-notification-unit-fake
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
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_paid_post_purchase()
    {
        Notification::fake();

        $timeline = Timeline::has('posts','>=',5)->has('followers','>=',1)->firstOrFail();
        $post = $timeline->posts[0];

        $creator = $timeline->user;
        $fan = $timeline->followers[0];

        Notification::send( $creator, new ResourcePurchased($post, $fan));
        Notification::assertSentTo( [$creator], ResourcePurchased::class );
    }

    // ----- %%% Timelines -----

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_timeline_new_follower()
    {
        Notification::fake();

        $timeline = Timeline::has('posts','>=',5)->has('followers','>=',1)->firstOrFail();

        $creator = $timeline->user;
        $fan = $timeline->followers[0];

        Notification::send( $creator, new TimelineFollowed($timeline, $fan));
        Notification::assertSentTo( [$creator], TimelineFollowed::class );
    }


    // ----- %%% Posts -----

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_post_like_received()
    {
        Notification::fake();

        $timeline = Timeline::has('posts','>=',5)->has('followers','>=',1)->firstOrFail();
        $post = $timeline->posts[0];

        $creator = $timeline->user;
        $fan = $timeline->followers[0];

        Notification::send( $creator, new LikeReceived($post, $fan));
        Notification::assertSentTo( [$creator], LikeReceived::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_post_comment_received()
    {
        Notification::fake();

        $comment = Comment::first();
        $sender = $comment->user;
        $receiver = $comment->post->timeline->user;
        Notification::send( $receiver, new CommentReceived($comment, $sender));
        Notification::assertSentTo( [$receiver], CommentReceived::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     * @group here0915
     */
    public function test_should_notify_post_tag_received()
    {
        Notification::fake();

        $timeline = Timeline::has('posts','>=',5)->has('followers','>=',1)->firstOrFail();
        $post = $timeline->posts[0];

        $creator = $timeline->user;
        $fan = $timeline->followers[0];

        Notification::send( $creator, new TagReceived($post, $fan));
        Notification::assertSentTo( [$creator], TagReceived::class );
    }

    // ----- %%% Auth -----

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_verify_email_sent_post_register()
    {
        Notification::fake();
        $user = User::first();
        $url = url(route('verification.verify', ['id' => $user->id, 'hash' => 'foo-bar']));
        $result = Notification::send( $user, new VerifyEmail($user, $url) );
        Notification::assertSentTo( [$user], VerifyEmail::class );
    }

    /**
     * @group lib-notification-unit-fake
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
     * @group lib-notification-unit-fake
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
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_password_reset()
    {
        Notification::fake();
        $user = User::first();
        $result = Notification::send( $user, new PasswordReset($user, ['token'=>$this->faker->uuid]) );
        Notification::assertSentTo( [$user], PasswordReset::class );
    }

    /**
     * @group lib-notification-unit-fake
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
        Notification::send( $user, new IdentityVerificationRequestSent($vr, $user));
        Notification::assertSentTo( [$user], IdentityVerificationRequestSent::class );
    }

    /**
     * @group lib-notification-unit-fake
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
     * @group lib-notification-unit-fake
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

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     * @group here0920
     */
    public function test_should_notify_invite_staff_manager()
    {
        Notification::fake();
        $creator = User::first();

        // Invite new staff user as manager
        $attrs = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->firstName,
            'email' => $this->faker->safeEmail,
            'role' => 'manager',
            'owner_id' => $creator->id,
            'token' => str_random(60),
            'creator_id' => $creator->id,
        ];

        $staff = Staff::create($attrs);

        Notification::send( $user, new InviteStaffManager($staff, $creator));
        Notification::assertSentTo( [$user], InviteStaffManager::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     * @group %TODO-here0920
     */
    public function test_should_notify_invite_staff_member()
    {
        Notification::fake();
        $creator = User::first();

        // Invite new staff user as member
        $attrs = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->firstName,
            'email' => $this->faker->safeEmail,
            'role' => 'member',
            'owner_id' => $creator->id,
            'token' => str_random(60),
            'creator_id' => $creator->id,
        ];

        $staff = Staff::create($attrs);

        Notification::send( $user, new InviteStaffMember($staff, $user));
        Notification::assertSentTo( [$user], InviteStaffMember::class );
    }

}

