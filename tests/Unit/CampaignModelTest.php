<?php
namespace Tests\Unit;

use DB;
use Illuminate\Foundation\Testing\WithFaker;

use Carbon\Carbon;
use Money\Money;
use Tests\TestCase;

use App\Payments\PaymentGateway;
use App\Enums\Financial\AccountTypeEnum;

use App\Models\Campaign;
use App\Models\User;
use App\Models\Post;
use App\Models\Timeline;
use App\Models\Subscription;
use App\Models\Shareable;
use App\Models\Casts\Money as CastsMoney;
use App\Enums\ShareableAccessLevelEnum;

use App\Enums\CampaignTypeEnum;

class CampaignModelTest extends TestCase
{
    use WithFaker;

    /**
     * @group lib-campaign-unit
     * @group regression
     * @group regression-unit
     * @group here0913
     */
    public function test_should_create_campaign()
    {
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
        $this->assertNotNull($campaign);
        $this->assertNotNull($campaign->id);

        //$sender = $timeline->followers->first(); // fan
        //$receiver = $campaign->creator; // creator
        //$amount = CastsMoney::USD($subPrice);

        // [ ] subscribe 2 fans and check that the campaign gets closed
    }


}
