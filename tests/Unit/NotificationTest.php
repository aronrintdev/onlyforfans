<?php
namespace Tests\Unit;

use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Config;

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
use App\Models\Staff;
use App\Models\Subscription;
use App\Models\Timeline;
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
        NotificationFacade::fake();
        $receiver = User::has('chatthreads')->firstOrFail();

        $chatthread = $receiver->chatthreads->first();
        $this->assertNotNull($chatthread);
        $this->assertNotNull($chatthread->id);

        $chatmessage = $chatthread->chatmessages->first();
        $this->assertNotNull($chatmessage);
        $this->assertNotNull($chatmessage->id);

        $sender = $chatmessage->sender;

        $result = NotificationFacade::send( $receiver, new MessageReceived($chatmessage, $sender) );
        NotificationFacade::assertSentTo( [$receiver], MessageReceived::class );
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
        NotificationFacade::fake();
        $result = NotificationFacade::send( $receiver, new NewReferralReceived($referral, $sender) );
        NotificationFacade::assertSentTo( [$receiver], NewReferralReceived::class );
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
        NotificationFacade::fake();
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
        $result = NotificationFacade::send( $receiver, new NewCampaignContributionReceived($campaign, $sender, ['amount'=>$amount]) );
        NotificationFacade::assertSentTo( [$receiver], NewCampaignContributionReceived::class );
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
        NotificationFacade::fake();
        $campaign = Campaign::first();
        $sender = $timeline->followers->first(); // fan
        $receiver = $campaign->creator; // creator
        $amount = Money::USD( $this->faker->numberBetween(1, 20) * 500 ); // %FIXME: should be associated with campaign?
        $result = NotificationFacade::send( $receiver, new NewCampaignContributionReceived($campaign, $sender, ['amount'=>$amount]) );
        NotificationFacade::assertSentTo( [$receiver], NewCampaignContributionReceived::class );
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
        NotificationFacade::fake();
        $campaign = Campaign::first();
        $sender = $timeline->followers->first(); // fan
        $receiver = $campaign->creator; // creator
        $amount = Money::USD( $this->faker->numberBetween(1, 20) * 500 ); // %FIXME: should be associated with campaign?
        $result = NotificationFacade::send( $receiver, new NewCampaignContributionReceived($campaign, $sender, ['amount'=>$amount]) );
        NotificationFacade::assertSentTo( [$receiver], NewCampaignContributionReceived::class );
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
        NotificationFacade::fake();
        $timeline = Timeline::has('followers', '>=', 1)->first(); // subscribable
        $sender = $timeline->followers->first(); // fan
        $receiver = $timeline->user; // creator
        $amount = Money::USD( $this->faker->numberBetween(1, 20) * 500 );
        $result = NotificationFacade::send( $receiver, new SubRenewalPaymentReceived($timeline, $sender, ['amount'=>$amount]) );
        NotificationFacade::assertSentTo( [$receiver], SubRenewalPaymentReceived::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_sub_renewal_payment_received_returning_subscriber()
    {
        NotificationFacade::fake();
        $timeline = Timeline::has('followers', '>=', 1)->first(); // subscribable
        $sender = $timeline->followers->first(); // fan
        $receiver = $timeline->user; // creator
        $amount = Money::USD( $this->faker->numberBetween(1, 20) * 500 );
        $result = NotificationFacade::send( $receiver, new SubRenewalPaymentReceivedReturningSubscriber($timeline, $sender, ['amount'=>$amount]) );
        NotificationFacade::assertSentTo( [$receiver], SubRenewalPaymentReceivedReturningSubscriber::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_new_sub_payment_received()
    {
        NotificationFacade::fake();
        $timeline = Timeline::has('followers', '>=', 1)->first(); // subscribable
        $sender = $timeline->followers->first(); // fan
        $receiver = $timeline->user; // creator
        $amount = Money::USD( $this->faker->numberBetween(1, 20) * 500 );
        $result = NotificationFacade::send( $receiver, new NewSubPaymentReceived($timeline, $sender, ['amount'=>$amount]) );
        NotificationFacade::assertSentTo( [$receiver], NewSubPaymentReceived::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_tip_received()
    {
        NotificationFacade::fake(); // this should bypass sending mail, even to SendGrid (?)

        $tip = Tip::where('tippable_type', 'posts')->first(); // the 'tip'
        $post = $tip->tippable; // the 'tippable'
        $sender = $tip->sender;
        $receiver = $tip->receiver;
        $result = NotificationFacade::send( $receiver, new TipReceived($post, $sender, ['amount'=>$tip->amount]) );
        NotificationFacade::assertSentTo( [$receiver], TipReceived::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_paid_post_purchase()
    {
        NotificationFacade::fake();

        $timeline = Timeline::has('posts','>=',5)->has('followers','>=',1)->firstOrFail();
        $post = $timeline->posts[0];

        $creator = $timeline->user;
        $fan = $timeline->followers[0];

        NotificationFacade::send( $creator, new ResourcePurchased($post, $fan));
        NotificationFacade::assertSentTo( [$creator], ResourcePurchased::class );
    }

    // ----- %%% Timelines -----

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_timeline_new_follower()
    {
        NotificationFacade::fake();

        $timeline = Timeline::has('posts','>=',5)->has('followers','>=',1)->firstOrFail();

        $creator = $timeline->user;
        $fan = $timeline->followers[0];

        NotificationFacade::send( $creator, new TimelineFollowed($timeline, $fan));
        NotificationFacade::assertSentTo( [$creator], TimelineFollowed::class );
    }


    // ----- %%% Posts -----

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_post_like_received()
    {
        NotificationFacade::fake();

        $timeline = Timeline::has('posts','>=',5)->has('followers','>=',1)->firstOrFail();
        $post = $timeline->posts[0];

        $creator = $timeline->user;
        $fan = $timeline->followers[0];

        NotificationFacade::send( $creator, new LikeReceived($post, $fan));
        NotificationFacade::assertSentTo( [$creator], LikeReceived::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_post_comment_received()
    {
        NotificationFacade::fake();

        $comment = Comment::first();
        $sender = $comment->user;
        $receiver = $comment->post->timeline->user;
        NotificationFacade::send( $receiver, new CommentReceived($comment, $sender));
        NotificationFacade::assertSentTo( [$receiver], CommentReceived::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_post_tag_received()
    {
        NotificationFacade::fake();

        $timeline = Timeline::has('posts','>=',5)->has('followers','>=',1)->firstOrFail();
        $post = $timeline->posts[0];

        $creator = $timeline->user;
        $fan = $timeline->followers[0];

        NotificationFacade::send( $creator, new TagReceived($post, $fan));
        NotificationFacade::assertSentTo( [$creator], TagReceived::class );
    }

    // ----- %%% Auth -----

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_verify_email_sent_post_register()
    {
        NotificationFacade::fake();
        $user = User::first();
        $url = url(route('verification.verify', ['id' => $user->id, 'hash' => 'foo-bar']));
        $result = NotificationFacade::send( $user, new VerifyEmail($user, $url) );
        NotificationFacade::assertSentTo( [$user], VerifyEmail::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_email_verified()
    {
        NotificationFacade::fake();
        $user = User::first();
        $result = NotificationFacade::send( $user, new EmailVerified($user) );
        NotificationFacade::assertSentTo( [$user], EmailVerified::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_password_changed()
    {
        NotificationFacade::fake();
        $user = User::first();
        $result = NotificationFacade::send( $user, new PasswordChanged($user) );
        NotificationFacade::assertSentTo( [$user], PasswordChanged::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_password_reset()
    {
        NotificationFacade::fake();
        $user = User::first();
        $result = NotificationFacade::send( $user, new PasswordReset($user, ['token'=>$this->faker->uuid]) );
        NotificationFacade::assertSentTo( [$user], PasswordReset::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_id_verification_pending()
    {
        NotificationFacade::fake();
        $user = User::first();
        $vr = Verifyrequest::create([
            'service_guid' => $this->faker->uuid,
            'vservice' => 'fake-service',
            'vstatus' => VerifyStatusTypeEnum::PENDING,
            'requester_id' => $user->id,
            'last_checked_at' => '2021-07-17 01:48:49',
        ]);
        NotificationFacade::send( $user, new IdentityVerificationRequestSent($vr, $user));
        NotificationFacade::assertSentTo( [$user], IdentityVerificationRequestSent::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_id_verification_verified()
    {
        NotificationFacade::fake();
        $user = User::first();
        $vr = Verifyrequest::create([
            'service_guid' => $this->faker->uuid,
            'vservice' => 'fake-service',
            'vstatus' => VerifyStatusTypeEnum::VERIFIED,
            'requester_id' => $user->id,
            'last_checked_at' => '2021-07-17 01:48:49',
        ]);
        NotificationFacade::send( $user, new IdentityVerificationVerified($vr, $user));
        NotificationFacade::assertSentTo( [$user], IdentityVerificationVerified::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_id_verification_rejected()
    {
        NotificationFacade::fake();
        $user = User::first();
        $vr = Verifyrequest::create([
            'service_guid' => $this->faker->uuid,
            'vservice' => 'fake-service',
            'vstatus' => VerifyStatusTypeEnum::REJECTED,
            'requester_id' => $user->id,
            'last_checked_at' => '2021-07-17 01:48:49',
        ]);
        NotificationFacade::send( $user, new IdentityVerificationRejected($vr, $user));
        NotificationFacade::assertSentTo( [$user], IdentityVerificationRejected::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_invite_staff_manager_via_guest()
    {
        //NotificationFacade::fake();
        $lPath = self::getLogPath();
        $isLogScanEnabled = Config::get('sendgrid.testing.scan_log_file_to_check_emails', false);
        if( $isLogScanEnabled ) {
            $fSizeBefore = $lPath ? filesize($lPath) : null;
        }

        $timeline = Timeline::has('posts','>=',1)->first(); 
        $creator = $timeline->user;

        $existingStaff = Staff::where('role', 'manager')->where('owner_id', $creator->id)->pluck('email')->toArray();
        $notInA = $existingStaff;
        $notInA[] = $creator->email;
        $preManager = User::whereNotIn('email', $notInA)->first();
        $this->assertNotNull($preManager->id??null);
        $this->assertFalse( in_array($preManager->id, $existingStaff) );
        $this->assertFalse( $preManager->id === $creator->id );

        // Invite new staff user as manager
        $payload = [
            'first_name' => $preManager->real_firstname ?? $preManager->name,
            'last_name' => $preManager->real_lastname ?? 'Smith',
            'email' => $preManager->email,
            'pending' => true, // %FIXME: this should be set in model default
            'role' => 'manager',
            'creator_id' => null, // %FIXME: what is this?
        ];

        $staff = Staff::create($attrs);
        $to = $this->faker->safeEmail;
        NotificationFacade::route('mail', $to)->notify(new InviteStaffManager($staff, $creator));

        if ( $isLogScanEnabled && $lPath ) {
            $fSizeAfter = filesize($lPath);
            if ( $fSizeBefore && $fSizeAfter && ($fSizeAfter > $fSizeBefore) ) {
                $fDiff = $fSizeAfter > $fSizeBefore;
                $fcontents = file_get_contents($lPath, false, null, -($fDiff-2));
                $this->assertStringContainsStringIgnoringCase('To: '.$to, $fcontents);
                $this->assertStringContainsStringIgnoringCase('been invited to become a manager', $fcontents);
                $this->assertStringContainsString('Subject: Invite Staff Manager', $fcontents);
                $this->assertStringContainsString('From:', $fcontents);
            }
        }
        //InviteStaffManager::sendGuestInvite($staff, $creator);
        //NotificationFacade::send( $user, new InviteStaffManager($staff, $creator));
        //NotificationFacade::assertSentTo( [$user], InviteStaffManager::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_invite_staff_manager_via_user()
    {
        NotificationFacade::fake();
        $lPath = self::getLogPath();
        $isLogScanEnabled = Config::get('sendgrid.testing.scan_log_file_to_check_emails', false);
        if( $isLogScanEnabled ) {
            $fSizeBefore = $lPath ? filesize($lPath) : null;
        }

        $creator = User::first();
        $preManager = 

        // %TODO: 2 cases: invitee is a registered user, invitee is not a registered user

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
        $to = $this->faker->safeEmail;
        NotificationFacade::route('mail', $to)->notify(new InviteStaffManager($staff, $creator));

        if ( $isLogScanEnabled && $lPath ) {
            $fSizeAfter = filesize($lPath);
            if ( $fSizeBefore && $fSizeAfter && ($fSizeAfter > $fSizeBefore) ) {
                $fDiff = $fSizeAfter > $fSizeBefore;
                $fcontents = file_get_contents($lPath, false, null, -($fDiff-2));
                $this->assertStringContainsStringIgnoringCase('To: '.$to, $fcontents);
                $this->assertStringContainsStringIgnoringCase('been invited to become a manager', $fcontents);
                $this->assertStringContainsString('Subject: Invite Staff Manager', $fcontents);
                $this->assertStringContainsString('From:', $fcontents);
            }
        }
        //InviteStaffManager::sendGuestInvite($staff, $creator);
        //NotificationFacade::send( $user, new InviteStaffManager($staff, $creator));
        //NotificationFacade::assertSentTo( [$user], InviteStaffManager::class );
    }

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     */
    public function test_should_notify_invite_staff_member_via_guest()
    {
        //NotificationFacade::fake();

        $lPath = self::getLogPath();
        $isLogScanEnabled = Config::get('sendgrid.testing.scan_log_file_to_check_emails', false);
        if( $isLogScanEnabled ) {
            $fSizeBefore = $lPath ? filesize($lPath) : null;
        }

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

        $to = $this->faker->safeEmail;
        NotificationFacade::route('mail', $to)->notify(new InviteStaffMember($staff, $creator));

        if ( $isLogScanEnabled && $lPath ) {
            $fSizeAfter = filesize($lPath);
            if ( $fSizeBefore && $fSizeAfter && ($fSizeAfter > $fSizeBefore) ) {
                $fDiff = $fSizeAfter > $fSizeBefore;
                $fcontents = file_get_contents($lPath, false, null, -($fDiff-2));
                $this->assertStringContainsStringIgnoringCase('To: '.$to, $fcontents);
                $this->assertStringContainsStringIgnoringCase('been added as a staff member', $fcontents);
                $this->assertStringContainsString('Subject: Invite Staff Member', $fcontents);
                $this->assertStringContainsString('From:', $fcontents);
            }
        }

        //NotificationFacade::send( $user, new InviteStaffMember($staff, $user));
        //NotificationFacade::assertSentTo( [$user], InviteStaffMember::class );
    }

}

