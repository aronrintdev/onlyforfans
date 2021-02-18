<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Session;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

use App\Models\User;
use App\Models\Timeline;

class RestSettingTest extends TestCase
{
    /**
     *  @group settings
     *  @group regression
     */
    public function test_can_index_creator_settings()
    {
        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $response = $this->actingAs($creator)->get('/'.$creator->username.'/settings/general');
        $response->assertStatus(200);
        $content = $response->content();
        $response->dump();

    }

    /**
     *  @group settings
     *  @group regression
     */
    public function test_can_index_fan_settings()
    {
        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $fan = $timeline->followers[0];
        $response = $this->actingAs($fan)->get('/'.$fan->username.'/settings/general');
        $response->assertStatus(200);
    }


    /**
     *  @group settings
     *  @group regression
     */
    public function test_can_creator_edit_name()
    {
        Session::start();

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $owner = $timeline->user;
        $response = $this->actingAs($owner)
            ->json('POST', '/'.$owner->username.'/settings/general', [
                'username' => $owner->username,
                'name' => 'New Name',
                'email' => $owner->email,
                '_token' => Session::token()
            ]);

        $response->assertStatus(200);

    }


    /**
     *  @group settings
     *  @group regression
     */
    public function test_can_creator_edit_username()
    {
        Session::start();
    
        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $owner = $timeline->user;
        $response = $this->actingAs($owner)
            ->json('POST', '/'.$owner->username.'/settings/general', [
                'username' => 'test.user',
                'name' => $owner->name,
                'email' => $owner->email,
                '_token' => Session::token()
            ]);

        $response->assertStatus(200);

    }


    /**
     *  @group settings
     *  @group regression
     */
    public function test_can_creator_edit_email()
    {
        Session::start();

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $owner = $timeline->user;
        $response = $this->actingAs($owner)
            ->json('POST', '/'.$owner->username.'/settings/general', [
                'username' => $owner->username,
                'name' => $owner->name,
                'email' => 'test.user@email.com',
                '_token' => Session::token()
            ]);

        $response->assertStatus(200);

    }


    /**
     *  @group settings
     *  @group regression
     */
    public function test_can_fan_edit_name()
    {
        Session::start();

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $fan = $timeline->followers[0];
        $response = $this->actingAs($fan)
            ->json('POST', '/'.$fan->username.'/settings/general', [
                'username' => $fan->username,
                'name' => 'New Name',
                'email' => $fan->email,
                '_token' => Session::token()
            ]);

        $response->assertStatus(200);

    }


    /**
     *  @group settings
     *  @group regression
     */
    public function test_can_fan_edit_username()
    {
        Session::start();
    
        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $fan = $timeline->followers[0];
        $response = $this->actingAs($fan)
            ->json('POST', '/'.$fan->username.'/settings/general', [
                'username' => 'test.user',
                'name' => $fan->name,
                'email' => $fan->email,
                '_token' => Session::token()
            ]);

        $response->assertStatus(200);

    }


    /**
     *  @group settings
     *  @group regression
     */
    public function test_can_fan_edit_email()
    {
        Session::start();

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $fan = $timeline->followers[0];
        $response = $this->actingAs($fan)
            ->json('POST', '/'.$fan->username.'/settings/general', [
                'username' => $fan->username,
                'name' => $fan->name,
                'email' => 'test.user@email.com',
                '_token' => Session::token()
            ]);

        $response->assertStatus(200);

    }


    /**
     *  @group settings
     *  @group regression
     */
    public function test_can_creator_set_localization()
    {
        Session::start();

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $response = $this->actingAs($creator)
            ->json('POST', '/'.$creator->username.'/settings/localization', [
                'timezone_id' => 15,
                'country' => 'US',
                'timezone' => 'GMT-05:00',
                'currency' => 'EUR',
                '_token' => Session::token()
            ]);

        $response->assertStatus(200);

    }

    /**
     *  @group settings
     *  @group regression
     */
    public function test_can_fan_set_localization()
    {
        Session::start();

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $fan = $timeline->followers[0];
        $response = $this->actingAs($fan)
            ->json('POST', '/'.$fan->username.'/settings/localization', [
                'timezone_id' => 15,
                'country' => 'US',
                'timezone' => 'GMT-05:00',
                'currency' => 'EUR',
                '_token' => Session::token()
            ]);

        $response->assertStatus(200);

    }


    /**
     *  @group settings
     *  @group regression
     */
    public function test_can_creator_set_subscription_price()
    {
        Session::start();

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
                '_token' => Session::token()
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
            '_token' => Session::token()
        ]);

        $response->assertStatus(302);
    }

    /**
     *  @group settings
     *  @group regression
     */
    public function test_can_creator_set_referral_rewards_enable()
    {
        Session::start();

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
            '_token' => Session::token()
        ]);

        $response->assertStatus(302);
    }

    /**
     *  @group settings
     *  @group regression
     */
    public function test_can_creator_set_enable_or_disable_follow_for_free()
    {
        Session::start();

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
            '_token' => Session::token()
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
            '_token' => Session::token()
        ]);

        $response->assertStatus(302);
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
