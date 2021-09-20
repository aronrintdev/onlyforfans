<?php
namespace Tests\Feature;

use Auth;
use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;
use Lunaweb\RecaptchaV3\Facades\RecaptchaV3;

use App\Models\Notification;
use App\Models\Timeline;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\Vault;
use App\Models\Vaultfolder;

class RestUsersTest extends TestCase
{
    use WithFaker;


    /**
     *  @group users
     *  @group regression
     *  @group regression-base
     */
    public function test_admin_can_list_users()
    {
        $timeline = Timeline::has('followers', '>=', 1)->firstOrFail();
        $creator = $timeline->user;

        $admin = User::where('id', '<>', $creator->id)->first();
        $admin->assignRole('super-admin'); // upgrade to admin!
        $admin->refresh();

        $expectedCount = User::count();
        $response = $this->actingAs($admin)->ajaxJSON('GET', route('users.index'), [ 'take'=>1000 ]);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $response->assertJsonStructure([
            'data' => [
                0 => [
                    'id', 
                    'email', 
                    'username', 
                    'created_at', 
                ],
            ],
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);
        $admin->removeRole('super-admin'); // revert (else future tests will fail)
        $this->assertEquals(1, $content->meta->current_page);

        $this->assertNotNull($content->data);
        $this->assertEquals($expectedCount, count($content->data));
        $this->assertObjectHasAttribute('username', $content->data[0]);
    }

    /**
     *  @group users
     *  @group regression
     *  @group regression-base
     */
    public function test_user_can_view_settings()
    {
        $timeline = Timeline::has('followers', '>=', 1)->firstOrFail();
        $creator = $timeline->user;
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('users.showSettings', $creator->id));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->data);
        $this->assertObjectHasAttribute('user_id', $content->data);
        $this->assertObjectHasAttribute('cattrs', $content->data);
        $this->assertObjectHasAttribute('weblinks', $content->data);
    }

    /**
     *  @group users
     *  @group regression
     *  @group regression-base
     *  @group fujio
     */
    public function test_user_can_login()
    {
        $user = User::first();
        $test_token = 'test';
        RecaptchaV3::shouldReceive('verify')
            ->with($test_token, 'login')
            ->once()
            ->andReturn(0.5);
        $payload = [ 'email' => $user->email, 'password' => 'foo-123', 'g-recaptcha-response' => $test_token];
        $response = $this->ajaxJSON('POST', '/login', $payload);
        $response->assertStatus(200);
    }

    /**
     *  @group users
     *  @group regression
     *  @group regression-base
     */
    public function test_user_can_register()
    {
        $lPath = self::getLogPath();
        $isLogScanEnabled = Config::get('sendgrid.testing.scan_log_file_to_check_emails', false);
        if( $isLogScanEnabled ) {
            $fSizeBefore = $lPath ? filesize($lPath) : null;
        }

        // Save the number of notify records to ensure we add one later...
        $notifyCountBefore = Notification::count();

        // ---------------------------------------------------------------
        // %%% Register the user
        // ---------------------------------------------------------------
        $testToken = 'test';
        RecaptchaV3::shouldReceive('verify')
            ->with($testToken, 'register')
            ->once()
            ->andReturn(0.5);

        $payload = [ 
            'name' => strtolower($this->faker->firstName.'1ac'), // a username
            'email' => $email = $this->faker->safeEmail,
            'password' => 'foo-123-456',
            'g-recaptcha-response' => $testToken,
            'tos' => true,
        ];
        $response = $this->ajaxJSON('POST', '/register', $payload);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'user' => [ 
                'id',
                'username',
                'email_verified',
                'referral_code',
                'created_at',
                'about',
                'is_verified',
                'verifyrequest',

                'avatar' => ['filepath'],
                'cover' => ['filepath'],

                'settings' => [
                    'id', 
                    'created_at', 
                    'is_creator', 

                    'birthdate',
                    'city',
                    'country',
                    'gender',
                    'has_allowed_nsfw',
                    'body_type',
                    'chest',
                    'waist',
                    'hips',
                    'arms',
                    'hair_color',
                    'eye_color',
                    'age',
                    'height',
                    'weight',
                    'education',
                    'language',
                    'ethnicity',
                    'profession',
                    'weblinks',

                    'cattrs' => [
                        'notifications' => [
                            'global' => [
                                'enabled',
                                'show_full_text',
                            ],
                            'campaigns' => [
                                'goal_achieved',
                                'new_contribution',
                            ],
                            'income' => [
                                'new_tip',
                                'new_subscription', // aka 'new subscriber'
                                'new_paid_post_purchase', // new purchase on on of my paid posts
                            ],
                            'messages' => [
                                'new_message', // new direct message received
                            ],
                            'posts' => [
                                'new_comment',
                                'new_like',
                            ],
                            'timelines' => [
                                'new_follower',
                            ],
                            'referrals' => [
                                'new_referral',
                            ],
                            'refunds' => [
                                'new_refund',
                            ],
                            'subscriptions' => [
                                'new_payment',
                            ],
                            'usertags' => [
                                'new_tag', // ex, when another user tags me in a post, etc
                            ],
                        ],

                        'subscriptions' => [
                            'price_per_1_months',
                        ], 
                        'localization', 
                        //'weblinks',  // move to its own column
                        'privacy', 
                        'blocked' => [
                            'ips',
                            'countries', 
                            'usernames', 
                        ],
                        'watermark',
                    ], // cattrs

                ], // settings

            ], // user

        ]);
        $content = json_decode($response->content());
        //dd($content, $payload);

        $newUser = User::find($content->user->id);

        // --- Check User ---

        $this->assertNotNull($newUser);
        $this->assertNotNull($newUser->id);
        $this->assertNull($newUser->email_verified_at);
        $this->assertFalse(!!$newUser->email_verified);
        $this->assertNotNull($newUser->verification_code);

        $this->assertNotNull($newUser->timeline);
        $this->assertNotNull($newUser->timeline->id);

        $this->assertNotNull($newUser->settings);
        $this->assertNotNull($newUser->settings->id);

        $this->assertNotNull($newUser->vaults);
        $this->assertEquals(1, $newUser->vaults->count());

        // --- Check Settings ---
        //   ~ certain notifications should be enabled by default at user registration

        $settings = UserSetting::where('user_id', $newUser->id)->first();
        $this->assertNotNull($settings);
        $this->assertNotNull($settings->id);

        $this->assertNotNull($settings->cattrs);
        $this->assertNotNull($settings->cattrs['notifications']);
        $this->assertNotNull($settings->cattrs['notifications']['global']);
        $this->assertNotNull($settings->cattrs['notifications']['global']['enabled']);

        // global
        $this->assertTrue( in_array('email', $settings->cattrs['notifications']['global']['enabled']) );
        $this->assertTrue( in_array('site', $settings->cattrs['notifications']['global']['enabled']) );

        // income
        $this->assertTrue(in_array('email', $settings->cattrs['notifications']['income']['new_tip']));
        $this->assertTrue(in_array('site', $settings->cattrs['notifications']['income']['new_tip']));
        $this->assertTrue(in_array('email', $settings->cattrs['notifications']['income']['new_subscription']));
        $this->assertTrue(in_array('site', $settings->cattrs['notifications']['income']['new_subscription']));
        $this->assertTrue(in_array('email', $settings->cattrs['notifications']['income']['new_paid_post_purchase']));
        $this->assertTrue(in_array('site', $settings->cattrs['notifications']['income']['new_paid_post_purchase']));

        // posts
        $this->assertTrue(in_array('email', $settings->cattrs['notifications']['posts']['new_comment']));
        $this->assertTrue(in_array('site', $settings->cattrs['notifications']['posts']['new_comment']));
        $this->assertTrue(in_array('email', $settings->cattrs['notifications']['posts']['new_like']));
        $this->assertTrue(in_array('site', $settings->cattrs['notifications']['posts']['new_like']));

        // timelines
        $this->assertTrue(in_array('email', $settings->cattrs['notifications']['timelines']['new_follower']));
        $this->assertTrue(in_array('site', $settings->cattrs['notifications']['timelines']['new_follower']));

        // messages
        $this->assertTrue(in_array('email', $settings->cattrs['notifications']['messages']['new_message']));
        $this->assertTrue(in_array('site', $settings->cattrs['notifications']['messages']['new_message']));

        // user tags
        $this->assertTrue(in_array('email', $settings->cattrs['notifications']['usertags']['new_tag']));
        $this->assertTrue(in_array('site', $settings->cattrs['notifications']['usertags']['new_tag']));

        // --- Check Timeline ---
        $timeline = Timeline::where('user_id', $newUser->id)->first();
        $this->assertNotNull($timeline);
        $this->assertNotNull($timeline->id);
        $this->assertTrue(!!$timeline->is_follow_for_free);

        // --- Check Vault --- %TODO
        $vault = Vault::where('user_id', $newUser->id)->first();
        $this->assertNotNull($vault);
        $this->assertNotNull($vault->id);
        $this->assertTrue(!!$vault->is_primary);
        $this->assertNotNull($vault->vaultfolders);
        $this->assertNotNull($vault->vaultfolders);
        $this->assertEquals(1, $vault->vaultfolders->count());

        // Check that one notification was added...
        $notifyCountAfter = Notification::count();
        $this->assertEquals($notifyCountBefore+1, $notifyCountAfter);
        $notifyCountBefore = $notifyCountAfter;

        $n = Notification::where('notifiable_id', $newUser->id)->orderBy('created_at', 'desc')->first();
        $this->assertNotNull($n);
        $this->assertNotNull($n->id);

        // set the created_at back a bit so it's disambuguated from Notifications below...
        $n->created_at = $n->created_at->subMinute();
        $n->save();

        // Check 'VerifyEmail' notification (the email with the link the user clicks to verify)
        $this->assertEquals('App\Notifications\VerifyEmail', $n->type);
        $this->assertEquals('users', $n->notifiable_type);

        $this->assertNotNull($n->data);
        $this->assertIsArray($n->data);
        $this->assertArrayHasKey('actor', $n->data);
        $this->assertArrayHasKey('username', $n->data['actor']);
        $this->assertArrayHasKey('name', $n->data['actor']);
        $this->assertEquals($newUser->username, $n->data['actor']['username']);

        if ( $isLogScanEnabled && $lPath ) {
            $fSizeAfter = filesize($lPath);
            if ( $fSizeBefore && $fSizeAfter && ($fSizeAfter > $fSizeBefore) ) {
                $fDiff = $fSizeAfter > $fSizeBefore;
                $fcontents = file_get_contents($lPath, false, null, -($fDiff-2));
                $this->assertStringContainsStringIgnoringCase('To: '.$newUser->email, $fcontents);
                $this->assertStringContainsStringIgnoringCase('Verify Email', $fcontents);
                $this->assertStringContainsString('Subject:', $fcontents);
                $this->assertStringContainsString('From:', $fcontents);
            }
            $fSizeBefore = filesize($lPath);
        }

        // ---------------------------------------------------------------
        // %%% Click on the link in the Verify Email to do the verification
        // ---------------------------------------------------------------

        $url = url( route('verification.verify', ['id' => $newUser->id, 'hash' => $newUser->verification_code]) );
        //$response = $this->actingAs($newUser)->ajaxJSON('GET', $url);
        $response = $this->ajaxJSON('GET', $url);
        $response->assertStatus(302);
        $newUser->refresh();
        $this->assertNotNull($newUser->email_verified_at);
        $this->assertTrue(!!$newUser->email_verified);

        // Check that one notification was added...
        $notifyCountAfter = Notification::count();
        $this->assertEquals($notifyCountBefore+1, $notifyCountAfter);
        $notifyCountBefore = $notifyCountAfter;

        $n = Notification::where('notifiable_id', $newUser->id)->orderBy('created_at', 'desc')->first();
        $this->assertNotNull($n);
        $this->assertNotNull($n->id);

        // set the created_at back a bit so it's disambuguated from Notifications below...
        $n->created_at = $n->created_at->subMinute();
        $n->save();

        // Check 'EmailVerified' notification (the email the user receives after confirming/verifying their email)
        $this->assertEquals('App\Notifications\EmailVerified', $n->type);
        $this->assertEquals('users', $n->notifiable_type);

        $this->assertNotNull($n->data);
        $this->assertIsArray($n->data);
        $this->assertArrayHasKey('actor', $n->data);
        $this->assertArrayHasKey('username', $n->data['actor']);
        $this->assertArrayHasKey('name', $n->data['actor']);
        $this->assertEquals($newUser->username, $n->data['actor']['username']);

        if ( $isLogScanEnabled && $lPath ) {
            $fSizeAfter = filesize($lPath);
            if ( $fSizeBefore && $fSizeAfter && ($fSizeAfter > $fSizeBefore) ) {
                $fDiff = $fSizeAfter > $fSizeBefore;
                $fcontents = file_get_contents($lPath, false, null, -($fDiff-2));
                $this->assertStringContainsStringIgnoringCase('To: '.$newUser->email, $fcontents);
                $this->assertStringContainsString('Subject: Email Verified', $fcontents);
                $this->assertStringContainsString('From:', $fcontents);
                $this->assertStringContainsStringIgnoringCase('Now that your email is verified', $fcontents);
            }
            $fSizeBefore = filesize($lPath);
        }
    }

    /**
     *  @group users
     *  @group regression
     *  @group regression-base
     *  @group fujio
     */
    public function test_user_cant_login_with_wrong_credientials()
    {
        $user = User::first();
        $test_token = 'test';
        RecaptchaV3::shouldReceive('verify')
            ->with($test_token, 'login')
            ->once()
            ->andReturn(0.5);
        $payload = [ 'email' => $user->email, 'password' => 'blahblah', 'g-recaptcha-response' => $test_token];
        $response = $this->ajaxJSON('POST', '/login', $payload);
        $response->assertUnauthorized(); // 401
    }

    /**
     *  @group users
     *  @group regression
     *  @group regression-base
     */
    public function test_user_can_reset_password()
    {
        $lPath = self::getLogPath();
        $isLogScanEnabled = Config::get('sendgrid.testing.scan_log_file_to_check_emails', false);
        if( $isLogScanEnabled ) {
            $fSizeBefore = $lPath ? filesize($lPath) : null;
        }

        $user = User::first();

        // Save the number of notify records to ensure we add one later...
        $notifyCountBefore = Notification::count();

        // Save the number of [password_resets] records to ensure we add one later...
        $passwordResetsCountBefore = DB::table('password_resets')->count();

        // Request to rest password
        $payload = [ 'email' => $user->email ];
        $response = $this->ajaxJSON('POST', '/forgot-password', $payload);
        //$response = $this->actingAs($user)->ajaxJSON('POST', '/forgot-password', $payload);
        //$content = json_decode($response->content());
        $response->assertStatus(200);

        if ( $isLogScanEnabled && $lPath ) {
            $fSizeAfter = filesize($lPath);
            if ( $fSizeBefore && $fSizeAfter && ($fSizeAfter > $fSizeBefore) ) {
                $fDiff = $fSizeAfter > $fSizeBefore;
                $fcontents = file_get_contents($lPath, false, null, -($fDiff-2));
                $this->assertStringContainsStringIgnoringCase('To: '.$user->email, $fcontents);
                $this->assertStringContainsStringIgnoringCase('Forgot Password', $fcontents);
                $this->assertStringContainsStringIgnoringCase('To reset your password, please click', $fcontents);
                $this->assertStringContainsString('Subject:', $fcontents);
                $this->assertStringContainsString('From:', $fcontents);
            }
            $fSizeBefore = $lPath ? filesize($lPath) : null;
        }
        $passwordResetsCountAfter = DB::table('password_resets')->count();
        $this->assertEquals($passwordResetsCountBefore+1, $passwordResetsCountAfter);

        $pr = DB::table('password_resets')->orderBy('created_at', 'desc')->where('email', $user->email)->first();
        $this->assertNotNull($pr);
        $this->assertNotNull($pr->id);
        $this->assertNotNull($pr->token);
        $resetToken = $pr->token;

        // This is not the correct implementation: AF-624
        /*
        $user->refresh();
        $resetToken = $user->remember_token;
        $verifyLink = config('base_url') . 'reset-password/' . $resetToken . '?email=' . urlencode($user->email);
         */

        //http://localhost:8000/reset-password/{token}?email=maybelle.macejkovic%40example.com

        // "Click" confirmation button in email

        // [ ] Submit new password form with token

        // [ ] Test login with new password

        // [ ] Check that one notification was added...

        // [ ] Check email in log
    }

    /**
     *  @group users
     *  @group regression
     *  @group regression-base
     */
    public function test_user_can_change_password()
    {
        $lPath = self::getLogPath();
        $isLogScanEnabled = Config::get('sendgrid.testing.scan_log_file_to_check_emails', false);
        if( $isLogScanEnabled ) {
            $fSizeBefore = $lPath ? filesize($lPath) : null;
        }

        $timeline = Timeline::has('followers', '>=', 1)->firstOrFail();
        $creator = $timeline->user;

        // Save the number of notify records to ensure we add one later...
        $notifyCountBefore = Notification::count();

        $oldPassword = 'foo-123'; // yes, hardcoded from the seeders
        $newPassword = 'bar-123';
        $payload = [ 'oldPassword' => $oldPassword, 'newPassword' => $newPassword ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('users.updatePassword', $creator->id), $payload);
        $response->assertStatus(200);

        Auth::logout();

        // Test login with old password
        $test_token = 'test';
        RecaptchaV3::shouldReceive('verify')
            ->with($test_token, 'login')
            ->once()
            ->andReturn(0.5);
        $payload = [ 'email' => $creator->email, 'password' => $oldPassword, 'g-recaptcha-response' => $test_token ];
        $response = $this->ajaxJSON('POST', '/login', $payload);
        $response->assertUnauthorized(); // 401

        // Test login with new password
        $test_token = 'test2';
        RecaptchaV3::shouldReceive('verify')
            ->with($test_token, 'login')
            ->once()
            ->andReturn(0.5);
        $payload = [ 'email' => $creator->email, 'password' => $newPassword, 'g-recaptcha-response' => $test_token ];
        $response = $this->ajaxJSON('POST', '/login', $payload);
        $response->assertStatus(200);

        // Check that one notification was added...
        $notifyCountAfter = Notification::count();
        $this->assertEquals($notifyCountBefore+1, $notifyCountAfter);

        $n = Notification::where('notifiable_id', $creator->id)->orderBy('created_at', 'desc')->first();
        $this->assertNotNull($n);
        $this->assertNotNull($n->id);

        // set the created_at back a bit so it's disambuguated from Notifications below...
        $n->created_at = $n->created_at->subMinute();
        $n->save();

        $this->assertEquals('App\Notifications\PasswordChanged', $n->type);
        $this->assertEquals('users', $n->notifiable_type);

        $this->assertNotNull($n->data);
        $this->assertIsArray($n->data);
        $this->assertArrayHasKey('actor', $n->data);
        $this->assertArrayHasKey('username', $n->data['actor']);
        $this->assertArrayHasKey('name', $n->data['actor']);
        $this->assertArrayHasKey('avatar', $n->data['actor']);
        $this->assertEquals($creator->username, $n->data['actor']['username']);

        if ( $isLogScanEnabled && $lPath ) {
            $fSizeAfter = filesize($lPath);
            if ( $fSizeBefore && $fSizeAfter && ($fSizeAfter > $fSizeBefore) ) {
                $fDiff = $fSizeAfter > $fSizeBefore;
                $fcontents = file_get_contents($lPath, false, null, -($fDiff-2));
                $this->assertStringContainsStringIgnoringCase('To: '.$creator->email, $fcontents);
                $this->assertStringContainsStringIgnoringCase('Password Changed', $fcontents);
                $this->assertStringContainsString('Subject:', $fcontents);
                $this->assertStringContainsString('From:', $fcontents);
            }
        }
    }

    /**
     *  @group users
     *  @group regression
     *  @group regression-base
     */
    public function test_user_can_get_session()
    {
        $timeline = Timeline::has('followers', '>=', 1)->firstOrFail();
        $creator = $timeline->user;
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('users.me'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'uiFlags' => [ 'isAdmin' ],
            'timeline' => [ 
                'userstats' => ['post_count', 'like_count', 'follower_count', 'following_count', 'subscribed_count', 'earnings'],
            ],
            'session_user' => [ 'email', 'name', 'avatar', 'about', 'roles', ],
        ]);
        $content = json_decode($response->content());
        $this->assertObjectNotHasAttribute('timeline', $content->session_user);
        $this->assertObjectNotHasAttribute('settings', $content->session_user);
        $this->assertObjectNotHasAttribute('posts', $content->timeline);
        $this->assertObjectNotHasAttribute('user', $content->timeline);
        $this->assertObjectNotHasAttribute('followers', $content->timeline);
        $this->assertObjectNotHasAttribute('subscribers', $content->timeline);
        $this->assertObjectNotHasAttribute('stories', $content->timeline);
    }

    /**
     *  @group users
     *  @group regression
     *  @group regression-base
     */
    public function test_admin_can_matchsearch_users()
    {
        $timeline = Timeline::has('followers', '>=', 1)->firstOrFail();
        $creator = $timeline->user;
        $admin = User::where('id', '<>', $creator->id)->first();
        $admin->assignRole('super-admin'); // upgrade to admin!
        $admin->refresh();
        $payload = [
            'term' => $creator->email,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('users.match', $admin->id), $payload);
        $admin->removeRole('super-admin'); // revert (else future tests will fail)
        $response->assertStatus(200);
    }

    /**
     *  @group NO-users
     *  @group NO-regression
     *  @group NO-regression-base
     */
    // Not put in regression as it adds new data to devdashboard.idmvalidate.com/verifications each time (?)
    public function test_should_send_verify_request_for_user()
    {
        $timeline = Timeline::has('followers', '>=', 1)->firstOrFail();
        $creator = $timeline->user;
        $usPhone = $faker->numberBetween(101, 999).'555'.$faker->numberBetween(0000, 9990);
        $payload = [
	        'mobile' => $usPhone,
            'firstname' => $faker->firstName,
            'lastname' => $faker->lastName,
	        'country' => 'US',
	        'dob' => $faker->date($format='Y-m-d', $max='2000-01-15'),
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('users.requestVerify', $payload));
        $content = json_decode($response->content());
        $response->assertStatus(200);
        dd($content);
    }


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

