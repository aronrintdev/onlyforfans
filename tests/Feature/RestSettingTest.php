<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

use App\Models\User;
use App\Models\Timeline;
use App\Models\Session;

class RestSettingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group settings
     *  @group here0429
     *  @group regression
     */
    public function test_can_update_single_notifications_setting_income_new_tip()
    {
        $timeline = Timeline::has('posts','>=',1)->first(); 
        $user = $timeline->user;

        $payload = [
            'income' => [
                'new_tip' => ['email', 'sms'],
                //'new_tip' => ['email'],
            ],
        ];
        $response = $this->actingAs($user)->ajaxJSON('PATCH', route('users.enableSetting', [$user->id, 'notifications']), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        //dd($content);
        $this->assertObjectHasAttribute('notifications', $content->cattrs);
        $this->assertObjectHasAttribute('income', $content->cattrs->notifications);
        $this->assertObjectHasAttribute('new_tip', $content->cattrs->notifications->income);
        $this->assertContains('email', $content->cattrs->notifications->income->new_tip);
        $this->assertContains('sms', $content->cattrs->notifications->income->new_tip);
        $this->assertNotContains('site', $content->cattrs->notifications->income->new_tip);
        /*
        $this->assertNotNull($content->post);
        $postR = $content->post;
        $this->assertNotNull($postR->description);
        $this->assertEquals($payload['description'], $postR->description);
         */
    }

    /**
     *  @group settings
     *  @group OFF-here0506
     *  @group regression
     */
    public function test_can_update_single_notifications_setting_post_new_comment()
    {
        $timeline = Timeline::has('posts','>=',1)->first(); 
        $user = $timeline->user;

        $payload = [
            'posts' => [
                //'new_comment' => ['email', 'sms'],
                'new_comment' => ['email'],
            ],
        ];
        $response = $this->actingAs($user)->ajaxJSON('PATCH', route('users.enableSetting', [$user->id, 'notifications']), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        //dd($content);
        $this->assertObjectHasAttribute('notifications', $content->cattrs);
        $this->assertObjectHasAttribute('posts', $content->cattrs->notifications);
        $this->assertObjectHasAttribute('new_comment', $content->cattrs->notifications->posts);
        $this->assertContains('email', $content->cattrs->notifications->posts->new_comment);
        $this->assertNotContains('site', $content->cattrs->notifications->posts->new_comment);
    }

    /**
     *  @group settings
     *  @group here0429
     *  @group regression
     */
    public function test_can_toggle_global_email_notifications_setting()
    {
        $timeline = Timeline::has('posts','>=',1)->first(); 
        $user = $timeline->user;

        $payload = [
            'global' => [
                'enabled' => ['email'],
            ],
        ];
        $response = $this->actingAs($user)->ajaxJSON('PATCH', route('users.enableSetting', [$user->id, 'notifications']), $payload);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertObjectHasAttribute('notifications', $content->cattrs);
        $this->assertObjectHasAttribute('global', $content->cattrs->notifications);
        $this->assertObjectHasAttribute('enabled', $content->cattrs->notifications->global);
        $this->assertContains('email', $content->cattrs->notifications->global->enabled);

        $payload = [
            'global' => [
                'enabled' => ['email'],
            ],
        ];
        $response = $this->actingAs($user)->ajaxJSON('PATCH', route('users.disableSetting', [$user->id, 'notifications']), $payload);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertObjectHasAttribute('notifications', $content->cattrs);
        $this->assertObjectHasAttribute('global', $content->cattrs->notifications);
        $this->assertObjectHasAttribute('enabled', $content->cattrs->notifications->global);
        $this->assertNotContains('email', $content->cattrs->notifications->global->enabled);
    }

    // ===============

    /**
     *  @group OFF-settings
     *  @group OFF-regression
     */
    public function test_can_index_creator_settings()
    {
        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $response = $this->actingAs($creator)->get('/'.$creator->username.'/settings/general');
        $response->assertStatus(200);
        $content = $response->content();
    }


    /**
     *  @group OFF-settings
     *  @group OFF-regression
     */
    public function test_can_index_fan_settings()
    {
        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $fan = $timeline->followers[0];
        $response = $this->actingAs($fan)->get('/'.$fan->username.'/settings/general');
        $response->assertStatus(200);
    }


    /**
     *  @group OFF-settings
     *  @group OFF-regression
     */
    public function test_can_creator_edit_name()
    {
        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();

        $owner = $timeline->user;
        $response = $this->actingAs($owner)
            ->json('POST', '/'.$owner->username.'/settings/general', [
                'username' => $owner->username,
                'name' => 'New Name',
                'email' => $owner->email,
                '_token' => csrf_token()
            ]);

        $response->assertStatus(200);

    }


    /**
     *  @group OFF-settings
     *  @group OFF-regression
     */
    public function test_can_creator_edit_username()
    {
    
        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $owner = $timeline->user;
        $response = $this->actingAs($owner)
            ->json('POST', '/'.$owner->username.'/settings/general', [
                'username' => 'test.user',
                'name' => $owner->name,
                'email' => $owner->email,
                '_token' => csrf_token()
            ]);

        $response->assertStatus(200);

    }


    /**
     *  @group settings
     *  @group OFF-regression
     */
    public function test_can_creator_edit_email()
    {

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $owner = $timeline->user;
        $response = $this->actingAs($owner)
            ->json('POST', '/'.$owner->username.'/settings/general', [
                'username' => $owner->username,
                'name' => $owner->name,
                'email' => 'test.user@email.com',
                '_token' => csrf_token()
            ]);

        $response->assertStatus(200);

    }


    /**
     *  @group settings
     *  @group OFF-regression
     */
    public function test_can_fan_edit_name()
    {

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $fan = $timeline->followers[0];
        $response = $this->actingAs($fan)
            ->json('POST', '/'.$fan->username.'/settings/general', [
                'username' => $fan->username,
                'name' => 'New Name',
                'email' => $fan->email,
                '_token' => csrf_token()
            ]);

        $response->assertStatus(200);

    }


    /**
     *  @group settings
     *  @group OFF-regression
     */
    public function test_can_fan_edit_username()
    {
    
        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $fan = $timeline->followers[0];
        $response = $this->actingAs($fan)
            ->json('POST', '/'.$fan->username.'/settings/general', [
                'username' => 'test.user',
                'name' => $fan->name,
                'email' => $fan->email,
                '_token' => csrf_token()
            ]);

        $response->assertStatus(200);

    }


    /**
     *  @group settings
     *  @group OFF-regression
     */
    public function test_can_fan_edit_email()
    {

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $fan = $timeline->followers[0];
        $response = $this->actingAs($fan)
            ->json('POST', '/'.$fan->username.'/settings/general', [
                'username' => $fan->username,
                'name' => $fan->name,
                'email' => 'test.user@email.com',
                '_token' => csrf_token()
            ]);

        $response->assertStatus(200);

    }


    /**
     *  @group settings
     *  @group OFF-regression
     */
    public function test_can_creator_set_localization()
    {

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $response = $this->actingAs($creator)
            ->json('POST', '/'.$creator->username.'/settings/localization', [
                'timezone_id' => 15,
                'country' => 'US',
                'timezone' => 'GMT-05:00',
                'currency' => 'EUR',
                '_token' => csrf_token()
            ]);

        $response->assertStatus(200);

    }

    /**
     *  @group settings
     *  @group OFF-regression
     */
    public function test_can_fan_set_localization()
    {

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $fan = $timeline->followers[0];
        $response = $this->actingAs($fan)
            ->json('POST', '/'.$fan->username.'/settings/localization', [
                'timezone_id' => 15,
                'country' => 'US',
                'timezone' => 'GMT-05:00',
                'currency' => 'EUR',
                '_token' => csrf_token()
            ]);

        $response->assertStatus(200);

    }


    /**
     *  @group settings
     *  @group OFF-regression
     */
    public function test_can_creator_set_subscription_price()
    {
        
        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $response = $this->actingAs($creator)
            ->json('POST', '/'.$creator->username.'/settings/subscription', [
                'subscribe_price_month' => 10.00,
                'is_follow_for_free' => 'on',
                'subscribe_price_3_month' => null,
                'subscribe_price_6_month' => null,
                'subscribe_price_year' => null,
                'referral-rewards' =>  'disabled',
                '_token' => csrf_token()
            ]);

        $response->assertStatus(302);

        $response = $this->actingAs($creator)
        ->json('POST', '/'.$creator->username.'/settings/subscription', [
            'subscribe_price_month' => null,
            'is_follow_for_free' => 'on',
            'subscribe_price_3_month' => 50.00,
            'subscribe_price_6_month' => null,
            'subscribe_price_year' => null,
            'referral-rewards' =>  'disabled',
            '_token' => csrf_token()
        ]);

        $response->assertStatus(302);
    }

    /**
     *  @group settings
     *  @group OFF-regression
     */
    public function test_can_creator_set_referral_rewards_enable()
    {

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;

        $response = $this->actingAs($creator)
        ->json('POST', '/'.$creator->username.'/settings/subscription', [
            'subscribe_price_month' => null,
            'is_follow_for_free' => 'on',
            'subscribe_price_3_month' => 50.00,
            'subscribe_price_6_month' => null,
            'subscribe_price_year' => null,
            'referral-rewards' =>  '1-free-month',
            '_token' => csrf_token()
        ]);

        $response->assertStatus(302);
    }

    /**
     *  @group settings
     *  @group OFF-regression
     */
    public function test_can_creator_set_enable_or_disable_follow_for_free()
    {

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;

        // Enable follow_for_free
        $response = $this->actingAs($creator)
        ->json('POST', '/'.$creator->username.'/settings/subscription', [
            'subscribe_price_month' => null,
            'is_follow_for_free' => 'on',
            'subscribe_price_3_month' => 50.00,
            'subscribe_price_6_month' => null,
            'subscribe_price_year' => null,
            'referral-rewards' =>  '1-free-month',
            '_token' => csrf_token()
        ]);
        $response->assertStatus(302);
    
        // Disable follow_for_free
        $response = $this->actingAs($creator)
        ->json('POST', '/'.$creator->username.'/settings/subscription', [
            'subscribe_price_month' => null,
            'subscribe_price_3_month' => 50.00,
            'subscribe_price_6_month' => null,
            'subscribe_price_year' => null,
            'referral-rewards' =>  '1-free-month',
            '_token' => csrf_token()
        ]);

        $response->assertStatus(302);
    }

    /**
     *  @group settings
     *  @group OFF-regression
     */
    public function test_can_creator_edit_profile()
    {

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;

        $response = $this->actingAs($creator)
            ->json('POST', '/'.$creator->username.'/settings/profile', [
                'about' => 'Distinctio aperiam quaerat sit sed. Sit iusto ea ut ea architecto quidem rerum. Aut sunt sed voluptatum nam sunt et quia.',
                'country' => 'US',
                'city' => 'Las Vegas',
                'gender' => 'male',
                'birthday' => '02/02/1990',
                '_token' => csrf_token()
            ]);

        $response->assertStatus(200);

        $response = $this->actingAs($creator)
            ->json('POST', '/'.$creator->username.'/settings/profile', [
                'about' => 'Distinctio aperiam quaerat sit sed. Sit iusto ea ut ea architecto quidem rerum. Aut sunt sed voluptatum nam sunt et quia.',
                'country' => 'US',
                'city' => 'Las Vegas',
                'gender' => 'female',
                'birthday' => '02/02/1990',
                '_token' => csrf_token()
            ]);

        $response->assertStatus(200);

        // Includes wishlist, website
        $response = $this->actingAs($creator)
            ->json('POST', '/'.$creator->username.'/settings/profile', [
                'about' => 'Distinctio aperiam quaerat sit sed. Sit iusto ea ut ea architecto quidem rerum. Aut sunt sed voluptatum nam sunt et quia.',
                'country' => 'US',
                'city' => 'Las Vegas',
                'gender' => 'female',
                'birthday' => '02/02/1990',
                'wishlist' => 'https://www.batz.com',
                'website' => 'http://www.sauer.biz/et-excepturi-numquam-recusandae-quia-eum-saepe-veritatis-rerum.html',
                '_token' => csrf_token()
            ]);

        $response->assertStatus(200);

    }

    /**
     *  @group settings
     *  @group OFF-regression
     */
    public function test_can_fan_edit_profile()
    {

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $fan = $timeline->followers[0];

        $response = $this->actingAs($fan)
            ->json('POST', '/'.$fan->username.'/settings/profile', [
                'about' => 'Distinctio aperiam quaerat sit sed. Sit iusto ea ut ea architecto quidem rerum. Aut sunt sed voluptatum nam sunt et quia.',
                'country' => 'US',
                'city' => 'Las Vegas',
                'gender' => 'male',
                'birthday' => '02/02/1990',
                '_token' => csrf_token()
            ]);

        $response->assertStatus(200);

        $response = $this->actingAs($fan)
            ->json('POST', '/'.$fan->username.'/settings/profile', [
                'about' => 'Distinctio aperiam quaerat sit sed. Sit iusto ea ut ea architecto quidem rerum. Aut sunt sed voluptatum nam sunt et quia.',
                'country' => 'US',
                'city' => 'Las Vegas',
                'gender' => 'female',
                'birthday' => '02/02/1990',
                '_token' => csrf_token()
            ]);

        $response->assertStatus(200);

        // Includes wishlist, website
        $response = $this->actingAs($fan)
            ->json('POST', '/'.$fan->username.'/settings/profile', [
                'about' => 'Distinctio aperiam quaerat sit sed. Sit iusto ea ut ea architecto quidem rerum. Aut sunt sed voluptatum nam sunt et quia.',
                'country' => 'US',
                'city' => 'Las Vegas',
                'gender' => 'female',
                'birthday' => '02/02/1990',
                'wishlist' => 'https://www.batz.com',
                'website' => 'http://www.sauer.biz/et-excepturi-numquam-recusandae-quia-eum-saepe-veritatis-rerum.html',
                '_token' => csrf_token()
            ]);

        $response->assertStatus(200);

    }


    /**
     *  @group settings
     *  @group OFF-regression
     */
    public function test_can_creator_edit_privacy() {

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;

        // // Who can post on your timeline = Everyone
        $response = $this->actingAs($creator)
            ->json('POST', '/'.$creator->username.'/settings/privacy', [
                'comment_privacy'=> 'everyone',
                'timeline_post_privacy' => 'everyone',
                'post_privacy' => 'everyone',
                'message_privacy' => 'everyone',
                '_token' => csrf_token()
            ]);

        $response->assertStatus(302);

        // Who can post on your timeline = People I follow
        $response = $this->actingAs($creator)
            ->json('POST', '/'.$creator->username.'/settings/privacy', [
                'comment_privacy' => 'everyone',
                'timeline_post_privacy' => 'only_follow',
                'post_privacy' => 'everyone',
                'message_privacy' => 'everyone',
                '_token' => csrf_token()
            ]);

        $response->assertStatus(302);


        // Who can post on your timeline = No One
        $response = $this->actingAs($creator)
            ->json('POST', '/'.$creator->username.'/settings/privacy', [
                'comment_privacy' => 'everyone',
                'timeline_post_privacy' => 'nobody',
                'post_privacy' => 'everyone',
                'message_privacy' => 'everyone',
                '_token' => csrf_token()
            ]);

        $response->assertStatus(302);

        // Who can comment on your posts = People I follow
        $response = $this->actingAs($creator)
            ->json('POST', '/'.$creator->username.'/settings/privacy', [
                'comment_privacy' => 'only_follow',
                'timeline_post_privacy' => 'everyone',
                'post_privacy' => 'everyone',
                'message_privacy' => 'everyone',
                '_token' => csrf_token()
            ]);

        $response->assertStatus(302);

        // Who can see your posts = People I follow
        $response = $this->actingAs($creator)
            ->json('POST', '/'.$creator->username.'/settings/privacy', [
                'comment_privacy' => 'everyone',
                'timeline_post_privacy' => 'everyone',
                'post_privacy' => 'only_follow',
                'message_privacy' => 'everyone',
                '_token' => csrf_token()
            ]);

        $response->assertStatus(302);
    
        // Who can message you = People I follow
        $response = $this->actingAs($creator)
            ->json('POST', '/'.$creator->username.'/settings/privacy', [
                'comment_privacy' => 'everyone',
                'timeline_post_privacy' => 'everyone',
                'post_privacy' => 'everyone',
                'message_privacy' => 'only_follow',
                '_token' => csrf_token()
            ]);

        $response->assertStatus(302);
    }

    /**
     *  @group settings
     *  @group OFF-regression
     */
    public function test_can_add_blocked_user() {
        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;

        $payload = [
            'ip_address' => '120.120.120.120',
            'country' => ''
        ];

        $response = $this->actingAs($creator)->ajaxJSON('POST', '/'.$creator->username.'/settings/block-profile', $payload);
        $response->assertStatus(201);
    }


    /**
     *  @group settings
     *  @group OFF-regression
     */
    public function test_can_add_watermark() {
        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;

        // Enable Watermark
        $response = $this->actingAs($creator)
            ->json('POST', '/'.$creator->username.'/settings/save-watermark-settings', [
                'watermark' => 1,
                'watermark_text' => 'watermark',
                'watermark_font_size' => '10',
                'watermark_position' => 'top',
                'watermark_font_color' => '#000000',
                '_token' => csrf_token()
            ]);

        $response->assertStatus(200);

        // Disable Watermark
        $response = $this->actingAs($creator)
            ->json('POST', '/'.$creator->username.'/settings/save-watermark-settings', [
                'watermark' => 0,
                '_token' => csrf_token()
            ]);

        $response->assertStatus(200);
    }

    /**
     *  @group settings
     *  @group OFF-regression
     */
    public function test_can_view_creator_earnings_overview()
    {
        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $response = $this->actingAs($creator)->get('/'.$creator->username.'/settings/earnings');
        $response->assertStatus(200);
    }

    /**
     *  @group settings
     *  @group OFF-regression
     */
    public function test_can_view_creator_login_sessions()
    {
        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $sessions = Session::where('user_id', $creator->id);
        $this->assertNotNull($sessions);
    }


    /**
     *  @group settings
     *  @group OFF-regression
     */
    public function test_can_view_creator_referrals()
    {
        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $response = $this->actingAs($creator)->get('/'.$creator->username.'/settings/affliates');
        $response->assertStatus(200);
        $referrals = User::where('affiliate_id', $creator->id)->where('id', '!=', $creator->id);;
        $this->assertNotNull($referrals);
    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
        $this->seed(TestDatabaseSeeder::class);
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}
