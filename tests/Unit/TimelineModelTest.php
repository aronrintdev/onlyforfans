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
use App\Models\Tip;
use App\Models\Casts\Money as CastsMoney;
use App\Enums\ShareableAccessLevelEnum;

use App\Enums\CampaignTypeEnum;

class TimelineModelTest extends TestCase
{
    use WithFaker;

    /**
     * @group lib-timeline-unit
     * @group regression
     * @group regression-unit
     */
    public function test_should_subscribe_to_timeline()
    {
        $timeline = Timeline::has('followers', '>=', 1)->first(); // subscribable
        $creator = $timeline->user;
        $userSettings = $creator->settings;

        // Set a subscrition price
        $subPriceInDollars = $this->faker->numberBetween(1, 20) * 5;
        $subPriceInCents = $subPriceInDollars * 100;
        $result = $userSettings->setValues('subscriptions', [ 'price_per_1_months' => $subPriceInDollars ]); // %FIXME TMP

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
        $fan->refresh();
        $timeline->refresh();

        // Follow relations will include subscribers
        $this->assertTrue( $fan->followedtimelines->contains( $timeline->id ) );
        $this->assertTrue( $timeline->followers->contains( $fan->id ) );
        //dd($result);

        // Check that follow is 'premium'...
        $shareable = Shareable::where('shareable_type', 'timelines')
            ->where('shareable_id', $timeline->id)
            ->where('sharee_id', $fan->id)
            ->orderBy('created_at', 'desc')->first();
        $this->assertNotNull($shareable);
        $this->assertNotNull($shareable->id);
        $this->assertEquals(ShareableAccessLevelEnum::PREMIUM, $shareable->access_level);

    }

    /**
     * @group lib-timeline-unit
     * @group regression
     * @group regression-unit
     * @group erik
     */
    public function test_should_unsubscribe_from_timeline()
    {
        $timeline = Timeline::has('followers', '>=', 1)->first(); // subscribable
        $creator = $timeline->user;
        $userSettings = $creator->settings;

        // --- First subscribe, then unsubscribe ---

        // Set a subscrition price
        $subPriceInDollars = $this->faker->numberBetween(1, 20) * 5;
        $subPriceInCents = $subPriceInDollars * 100;
        $result = $userSettings->setValues('subscriptions', [ 'price_per_1_months' => $subPriceInDollars ]); // %FIXME TMP

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
        $fan->refresh();
        $timeline->refresh();

        // Follow relations will include subscribers
        $this->assertTrue( $fan->followedtimelines->contains( $timeline->id ) );
        $this->assertTrue( $timeline->followers->contains( $fan->id ) );
        //dd($result);

        // Check that follow is 'premium'...
        $shareable = Shareable::where('shareable_type', 'timelines')
            ->where('shareable_id', $timeline->id)
            ->where('sharee_id', $fan->id)
            ->orderBy('created_at', 'desc')->first();
        $this->assertNotNull($shareable);
        $this->assertNotNull($shareable->id);
        $this->assertEquals(ShareableAccessLevelEnum::PREMIUM, $shareable->access_level);

        // invoke cancel on subscription
        $sub = Subscription::where('access_level', ShareableAccessLevelEnum::PREMIUM)
            ->where('subscribable_type', 'timelines')
            ->where('subscribable_id', $timeline->id)
            ->where('user_id', $fan->id) // subscriber
            ->where('account_id', $subscriberAccount->id)
            ->orderBy('created_at', 'desc')->first();
        $this->assertNotNull($sub);
        $this->assertNotNull($sub->id);
        $this->assertTrue(!!$sub->active);

        $sub->cancel();

        $sub->refresh();
        $fan->refresh();
        $timeline->refresh();

        $this->assertFalse(!!$sub->active);
        $this->assertFalse( $fan->followedtimelines->contains( $timeline->id ) );
        $this->assertFalse( $timeline->followers->contains( $fan->id ) );
    }

}
