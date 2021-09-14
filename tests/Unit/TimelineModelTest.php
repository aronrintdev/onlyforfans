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
use App\Models\Tip;
use App\Models\Casts\Money as CastsMoney;

use App\Enums\CampaignTypeEnum;

class TimelineModelTest extends TestCase
{
    use WithFaker;

    /**
     * @group lib-notification-unit-fake
     * @group regression
     * @group regression-unit
     * @group here0913
     */
    public function test_should_subscribe_to_timeline()
    {
        $timeline = Timeline::has('followers', '>=', 1)->first(); // subscribable
        $creator = $timeline->user;
        $userSettings = $creator->settings;

        // Set a subscrition price
        $subPriceInCents = $this->faker->numberBetween(1, 20) * 500;
        $result = $userSettings->setValues('subscriptions', [ 'price_per_1_months' => $subPriceInCents ]); // price in cents


        $nonfan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->firstOrFail();
        $this->assertFalse( $nonfan->followedtimelines->contains( $timeline->id ) );
        $this->assertFalse( $timeline->followers->contains( $nonfan->id ) );

        // Account to emulate a credit card payment (ie, 'IN')
        $subscriberAccount = $nonfan->financialAccounts()->where('type', AccountTypeEnum::IN)->with('resource')->firstOrFail();

        $payload = [
            'account_id' => $subscriberAccount->id,
            'amount' => $subPriceInCents,
        ];
        $price = CastsMoney::toMoney($payload['amount'], 'USD');

        $paymentGateway = new PaymentGateway();
        $result = $paymentGateway->subscribe($subscriberAccount, $timeline, $price);
        $this->assertTrue($result['success']);
        $this->assertTrue($result['faked']);

        // nonfan is now a fan
        $fan = $nonfan;

        // Follow relations will include subscribers
        $this->assertTrue( $fan->followedtimelines->contains( $timeline->id ) );
        $this->assertTrue( $timeline->followers->contains( $fan->id ) );
        //dd($result);

        // %TODO: check that follow is 'premium'...

    }

}
