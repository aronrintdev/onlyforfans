<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use DB;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;
use App\Enums\CampaignTypeEnum;
use App\Models\Campaign;
use App\Models\Post;
use App\Models\Timeline;
use App\Models\User;

class RestCampaignsTest extends TestCase
{
    use WithFaker;

    /**
     *  @group campaigns
     *  @group regression
     *  @group regression-base
     */
    public function test_can_create_discount_promotion()
    {
        $timeline = Timeline::where('price', '>', 0)->first();
        $creator = $timeline->user;

        // Make sure subscription price is high enough so discount keeps us above $3 min
        $subPriceInDollars = $this->faker->numberBetween(100, 300);
        $subPriceInCents = $subPriceInDollars * 100;
        // $result = $creator->settings->setValues('subscriptions', [ '1_month' => [ 'amount' => $subPriceInCents,
        // 'currency' => 'USD' ] ]); // %FIXME
        $timeline->updateOneMonthPrice($timeline->asMoney($subPriceInCents));
        $creator->refresh();

        $priorCampaign = null;

        // --- (1a) unlimited subcribers ---
        $payload = [
            'type' => CampaignTypeEnum::DISCOUNT,
            'has_new' => true,
            'has_expired' => true,
            'subscriber_count' => 0,
            'offer_days' => $this->faker->numberBetween(1, 20),
            'discount_percent' => $this->faker->numberBetween(1, 95),
            'trial_days' => $this->faker->numberBetween(1, 20),
            'message' => $this->faker->realText,
        ];

        $response = $this->actingAs($creator)->ajaxJSON('POST', route('campaigns.store'), $payload);
        $content = json_decode($response->content());
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [ 'type', 'active', 'has_new', 'has_expired', 'targeted_customer_group', 'subscriber_count', 'is_subscriber_count_unlimited', 'offer_days', 'discount_percent', 'trial_days', 'message', 'created_at' ],
        ]);

        $data = $content->data;
        //dd($content);
        $this->assertTrue($data->is_subscriber_count_unlimited);
        $this->assertTrue($data->has_new);
        $this->assertTrue($data->has_expired);
        $this->assertEquals($payload['offer_days'], $data->offer_days);
        $this->assertEquals($payload['trial_days'], $data->trial_days);
        $this->assertEquals($payload['discount_percent'], $data->discount_percent);
        $this->assertEquals($payload['message'], $data->message);
        $this->assertTrue($data->active);
        //dd($content);

        // --- (1b) unlimited subcribers ---
        $payload['subscriber_count'] = null;
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('campaigns.store'), $payload);
        $response->assertStatus(201);
        $priorCampaign = Campaign::findOrFail($data->id);
        $content = json_decode($response->content());
        $data = $content->data;
        $this->assertTrue($data->is_subscriber_count_unlimited);
        $this->assertTrue($data->active);
        $this->assertFalse(!!$priorCampaign->active);

        // --- (1c) unlimited subcribers ---
        unset($payload['subscriber_count']);
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('campaigns.store'), $payload);
        $response->assertStatus(201);
        $priorCampaign = Campaign::findOrFail($data->id);
        $content = json_decode($response->content());
        $data = $content->data;
        $this->assertTrue($data->is_subscriber_count_unlimited);
        $this->assertTrue($data->active);
        $this->assertFalse(!!$priorCampaign->active);

        // --- (2) limited subcribers ---
        $payload['subscriber_count'] = 10;
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('campaigns.store'), $payload);
        $response->assertStatus(201);
        $priorCampaign = Campaign::findOrFail($data->id);
        $content = json_decode($response->content());
        $data = $content->data;
        $this->assertFalse($data->is_subscriber_count_unlimited);
        $this->assertEquals($payload['subscriber_count'], $data->subscriber_count);
        $this->assertTrue($data->active);
        $this->assertFalse(!!$priorCampaign->active);
    }

    /**
     *  @group campaigns
     *  @group regression
     *  @group regression-base
     *  @group here0910
     */
    public function test_can_cancel_discount_promotion() 
    {
        $timeline = Timeline::where('price', '>', 0)->first();
        $creator = $timeline->user;

        // --- (1a) unlimited subcribers ---
        $payload = [
            'type' => CampaignTypeEnum::DISCOUNT,
            'has_new' => true,
            'has_expired' => true,
            'subscriber_count' => 0,
            'offer_days' => $this->faker->numberBetween(1, 20),
            'discount_percent' => $this->faker->numberBetween(1, 95),
            'trial_days' => $this->faker->numberBetween(1, 20),
            'message' => $this->faker->realText,
        ];

        $response = $this->actingAs($creator)->ajaxJSON('POST', route('campaigns.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $data = $content->data;

        $campaign = Campaign::findOrFail($data->id);
        $this->assertTrue(!!$campaign->active);

        $response = $this->actingAs($creator)->ajaxJSON('POST', route('campaigns.stop'));
        $campaign->refresh();
        $this->assertFalse(!!$campaign->active);

    }


    /**
     *  @group campaigns
     *  @group regression
     *  @group emojis
     *  @group regression-base
     */
    public function test_can_create_discount_promotion_with_emoji_text()
    {
        $timeline = Timeline::where('price', '>', 0)->first();
        $creator = $timeline->user;
        $settings = $creator->settings;
        $cattrs = $settings->cattrs;
        $cattrs['subscriptions']['price_per_1_months'] = 500;
        $creator->settings->cattrs = $cattrs;
        $creator->save();

        $EMOJI_TEXT = 'text with emoji 😘';

        $payload = [
            'type' => CampaignTypeEnum::DISCOUNT,
            'has_new' => true,
            'has_expired' => true,
            'subscriber_count' => 0,
            'offer_days' => $this->faker->numberBetween(1, 20),
            'discount_percent' => 5,
            'trial_days' => $this->faker->numberBetween(1, 20),
            'message' => $EMOJI_TEXT,
        ];

        $response = $this->actingAs($creator)->ajaxJSON('POST', route('campaigns.store'), $payload);
        $content = json_decode($response->content());
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [ 'type', 'active', 'has_new', 'has_expired', 'targeted_customer_group', 'subscriber_count', 'is_subscriber_count_unlimited', 'offer_days', 'discount_percent', 'trial_days', 'message', 'created_at' ],
        ]);

        $data = $content->data;
        $this->assertTrue($data->is_subscriber_count_unlimited);
        $this->assertTrue($data->has_new);
        $this->assertTrue($data->has_expired);
        $this->assertEquals($payload['offer_days'], $data->offer_days);
        $this->assertEquals($payload['trial_days'], $data->trial_days);
        $this->assertEquals($payload['discount_percent'], $data->discount_percent);
        $this->assertEquals($payload['message'], $data->message);
        $this->assertTrue($data->active);
    }

    // ------------------------------

    protected function setUp() : void {
        parent::setUp();
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

