<?php
namespace Tests\Feature;

use Auth;
use DB;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;
use App\Models\Timeline;
use App\Models\User;
use Lunaweb\RecaptchaV3\Facades\RecaptchaV3;

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
        $this->assertObjectHasAttribute('about', $content->data);
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
     *  @group here0914a
     */
    public function test_user_can_register()
    {
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

                    'about',
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
                        'weblinks', 
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

        // --- Check that certain notifications are enabled by default at user registration ---

        // global
        $this->assertTrue(in_array('email', $content->user->settings->cattrs->notifications->global->enabled));
        $this->assertTrue(in_array('site', $content->user->settings->cattrs->notifications->global->enabled));

        // income
        $this->assertTrue(in_array('email', $content->user->settings->cattrs->notifications->income->new_tip));
        $this->assertTrue(in_array('site', $content->user->settings->cattrs->notifications->income->new_tip));
        $this->assertTrue(in_array('email', $content->user->settings->cattrs->notifications->income->new_subscription));
        $this->assertTrue(in_array('site', $content->user->settings->cattrs->notifications->income->new_subscription));
        $this->assertTrue(in_array('email', $content->user->settings->cattrs->notifications->income->new_paid_post_purchase));
        $this->assertTrue(in_array('site', $content->user->settings->cattrs->notifications->income->new_paid_post_purchase));

        // posts
        $this->assertTrue(in_array('email', $content->user->settings->cattrs->notifications->posts->new_comment));
        $this->assertTrue(in_array('site', $content->user->settings->cattrs->notifications->posts->new_comment));
        $this->assertTrue(in_array('email', $content->user->settings->cattrs->notifications->posts->new_like));
        $this->assertTrue(in_array('site', $content->user->settings->cattrs->notifications->posts->new_like));

        // timelines
        $this->assertTrue(in_array('email', $content->user->settings->cattrs->notifications->timelines->new_follower));
        $this->assertTrue(in_array('site', $content->user->settings->cattrs->notifications->timelines->new_follower));

        // messages
        $this->assertTrue(in_array('email', $content->user->settings->cattrs->notifications->messages->new_message));
        $this->assertTrue(in_array('site', $content->user->settings->cattrs->notifications->messages->new_message));

        // user tags
        $this->assertTrue(in_array('email', $content->user->settings->cattrs->notifications->usertags->new_tag));
        $this->assertTrue(in_array('site', $content->user->settings->cattrs->notifications->usertags->new_tag));
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
     *  @group fujio
     */
    public function test_user_can_change_password()
    {
        $timeline = Timeline::has('followers', '>=', 1)->firstOrFail();
        $creator = $timeline->user;

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
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('users.verify-request', $payload));
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

