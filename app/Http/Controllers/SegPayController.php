<?php

namespace App\Http\Controllers;

use App\Enums\Financial\AccountTypeEnum;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Enums\PaymentTypeEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Helpers\Tippable as TippableHelpers;
use App\Helpers\Purchasable as PurchasableHelpers;
use App\Helpers\Subscribable as SubscribableHelpers;
use App\Interfaces\Purchaseable;
use App\Interfaces\Subscribable;
use App\Interfaces\Tippable;
use App\Jobs\FakeSegpayPayment;
use App\Models\Financial\Account;
use App\Models\Financial\SegpayCard;
use App\Rules\InEnum;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Models\Casts\Money as MoneyCast;
use App\Models\Financial\SegpayCall;
use App\Models\Subscription;
use Illuminate\Contracts\Container\BindingResolutionException;
use InvalidArgumentException;
use Carbon\Exceptions\InvalidCastException;
use Carbon\Exceptions\InvalidIntervalException;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class SegPayController extends Controller
{

    // Iframe Pay Page Url generation, Note: depreciated
    #region PayPageUrl Generation
    /**
     * Generate the pay page Url
     * @return string
     */
    public function generatePayPageUrl(Request $request)
    {
        if (isset($request->item)) {
            $item = PurchasableHelpers::getPurchasableItem($request->item);
            $price = $item->formatMoneyDecimal($item->price);
        } else {
            $price = $request->price;
        }

        if (!isset($price)) {
            abort(400, 'Price or item is required');
        }

        $client = new Client();
        $response = $client->request('GET', Config::get('segpay.dynamicTransUrl'), [
            'query' => [ 'value' => $price, ],
        ]);

        $priceEncode = simplexml_load_string($response->getBody(), 'SimpleXMLElement', LIBXML_NOCDATA)->__toString();

        $baseUrl = Config::get('segpay.secure') ? 'https://' : 'http://';
        $baseUrl .= Config::get('segpay.baseUrl');
        $packageId = Config::get('segpay.packageId');
        $pricePointId = Config::get('segpay.pricePointId');
        $appName = Config::get('app.name');
        $env = Config::get('app.env');

        $xEticketid = "{$packageId}:{$pricePointId}";
        $description = urlencode('All Fans Purchase');
        $save = ($request->save) ? '1' : '0';
        $userId = Auth::user()->getKey();

        $url = "{$baseUrl}?x-eticketid={$xEticketid}&amount={$price}&dynamictrans={$priceEncode}&dynamicdesc={$description}&app={$appName}&env={$env}&user_id={$userId}&save={$save}&type=purchase";
        if (isset($item)) {
            $url .= "&item_id={$item->getKey()}&item_type={$item->getMorphString()}";
        }

        // Generate Hash signature
        $secret = Config::get('segpay.secret');
        $body = "&app={$appName}&env={$env}&price={$price}&user_id={$userId}&save={$save}&type=purchase";
        if (isset($item)) {
            $body .= "&item_id={$item->getKey()}&item_type={$item->getMorphString()}";
        }
        $hash = hash_hmac('sha256', $body, $secret, false);
        $url .= "&REF1={$hash}";

        return $url;
    }

    /**
     * Generate the pay page url for a tip
     * @param Request $request
     * @return string
     */
    public function generateTipPayPageUrl(Request $request)
    {
        if (isset($request->item)) {
            $item = TippableHelpers::getTippableItem($request->item);
            $price = $item->formatMoneyDecimal($item->price);
        } else {
            $price = $request->price;
        }

        if (!isset($price)) {
            abort(400, 'Price or item is required');
        }

        $client = new Client();
        $response = $client->request('GET', Config::get('segpay.dynamicTransUrl'), [
            'query' => ['value' => $price,],
        ]);

        $priceEncode = simplexml_load_string($response->getBody(), 'SimpleXMLElement', LIBXML_NOCDATA)->__toString();

        $baseUrl = Config::get('segpay.secure') ? 'https://' : 'http://';
        $baseUrl .= Config::get('segpay.baseUrl');
        $packageId = Config::get('segpay.packageId');
        $pricePointId = Config::get('segpay.pricePointId');
        $appName = Config::get('app.name');
        $env = Config::get('app.env');

        $xEticketid = "{$packageId}:{$pricePointId}";
        $description = urlencode('All Fans Tip');
        $save = ($request->save) ? '1' : '0';
        $userId = Auth::user()->getKey();

        $url = "{$baseUrl}?x-eticketid={$xEticketid}&amount={$price}&dynamictrans={$priceEncode}&dynamicdesc={$description}&app={$appName}&env={$env}&user_id={$userId}&save={$save}&type=tip";
        if (isset($item)) {
            $url .= "&item_id={$item->getKey()}&item_type={$item->getMorphString()}";
        }

        // Generate Hash signature
        $secret = Config::get('segpay.secret');
        $body = "&app={$appName}&env={$env}&price={$price}&user_id={$userId}&save={$save}&type=tip";
        if (isset($item)) {
            $body .= "&item_id={$item->getKey()}&item_type={$item->getMorphString()}";
        }
        $hash = hash_hmac('sha256', $body, $secret, false);
        $url .= "&REF1={$hash}";

        return $url;
    }

    #endregion PayPageUrl Generation


    /**
     * Payment session for SegPay Segments when entering a new card
     *
     * @param Request $request
     * @return array
     */
    public function getPaymentSession(Request $request)
    {
        $request->validate([
            'item' => 'required|uuid',
            'type' => [ 'required', new InEnum(new PaymentTypeEnum())],
            'price' => 'required',
            'currency' => 'required',
        ]);

        // Get payment item
        if ($request->type === PaymentTypeEnum::PURCHASE) {
            $description = Config::get('segpay.description.purchase', 'All Fans Purchase');
        } else if ($request->type === PaymentTypeEnum::TIP) {
            $description = Config::get('segpay.description.tip', 'All Fans Tip');
        } else if ($request->type === PaymentTypeEnum::SUBSCRIPTION) {
            $description = Config::get('segpay.description.subscription', 'All Fans Subscription');
        }

        $item = $this->getItem($request);

        if (!isset($item)) {
            abort(400, 'Bad type or item');
        }

        $price = MoneyCast::toMoney($request->price, $request->currency);

        // Validate Price
        if (!$item->verifyPrice($price)) {
            abort(400, 'Invalid Price');
        }

        // If environment variable is set, fake results
        if (Config::get('segpay.fake') === true && Config::get('app.env') !== 'production') {
            return [
                'id' => 'faked',
                'pageId' => 'faked',
                'expirationDatTime' => Carbon::now()->addHour(),
            ];
        }

        // Get payment session
        $query = [
            'tokenId' => Config::get('segpay.paymentSessions.token'),
            'dynamicDescription' => urlencode($description),
            'dynamicInitialAmount' => $item->formatMoneyDecimal($price),
        ];
        $packageId = Config::get('segpay.packageId');

        if ($request->type === PaymentTypeEnum::SUBSCRIPTION) {
            $query = array_merge($query, [
                'dynamicInitialDurationInDays'   => 30,
                'dynamicRecurringAmount'         => $item->formatMoneyDecimal($price),
                'dynamicRecurringDurationInDays' => 30,
            ]);
            $packageId = Config::get('segpay.dynamicPackageId');
        }

        $client = new Client();
        $response = $client->request('GET', Config::get('segpay.paymentSessions.baseUrl'), [
            'query' => $query,
        ]);

        $segpay = json_decode($response->getBody(), true);

        return [
            'id' => $segpay['id'],
            'pageId' => $segpay['pageId'],
            'expirationDateTime' => $segpay['expirationDateTime'],
            'packageId' => $packageId,
        ];
    }

    /**
     * Confirms a payment with saved card
     *
     * @param Request $request
     * @return void
     */
    public function paymentConfirmation(Request $request)
    {
        $request->validate([
            'item'     => 'required|uuid',
            'type'     => ['required', new InEnum(new PaymentTypeEnum())],
            'price'    => 'required',
            'currency' => 'required',
            'method'   => 'required|uuid',
        ]);

        // Get payment item
        $item = $this->getItem($request);
        if (!isset($item)) {
            abort(400, 'Bad type or item');
        }

        $price = MoneyCast::toMoney($request->price, $request->currency);
        if (!$item->verifyPrice($price)) {
            abort(400, 'Invalid Price');
        }

        $account = Account::with('resource')->find($request->method);

        if ($account->resource->token === 'fake') {
            return $this->fakeConfirmation($request);
        }

        if ($request->type === PaymentTypeEnum::PURCHASE) {
            $segpayCall = SegpayCall::confirmPurchase($account, $price, $item);
        }

        if ($request->type === PaymentTypeEnum::TIP) {
            $segpayCall = SegpayCall::confirmTip($account, $price, $item);
        }

        if ($request->type === PaymentTypeEnum::SUBSCRIPTION) {
            // TODO: Reminder to refactor this, much can be moved to models

            // Verify subscription has not already been created
            if (
                Subscription::where('user_id', Auth::user()->getKey())
                    ->where('subscribable_id', $item->getKey())
                    ->whereNotNull('canceled_at')
                    ->count() > 0
            ) {
                abort(400, 'Already have subscription');
            }

            // Verify not resubscribing within waiting period
            if (
                Subscription::where('user_id', Auth::user()->getKey())
                    ->where('subscribable_id', $item->getKey())
                    ->canceled()->where(
                        'canceled_at',
                        '>=',
                        Carbon::now()->subtract(
                            Config::get('subscriptions.resubscribeWaitPeriod.unit'),
                            Config::get('subscriptions.resubscribeWaitPeriod.interval')
                        )
                    )->count() > 0
            ) {
                abort(400, 'Too soon to resubscribe');
            }

            // Create subscription
            $subscription = $account->createSubscription($item, $price, [
                'manual_charge' => false,
            ]);

            // Send Segpay One click call
            $segpayCall = SegpayCall::confirmSubscription($account, $price, $subscription);
        }

        if (isset($segpayCall->failed_at)) {
            abort(500, 'Error Processing Payment');
        }
        return;
    }

    /**
     * Fake a segpay purchase if system allows segpay fakes
     *
     * @param Request $request
     * @return void
     */
    public function fake(Request $request)
    {
        $this->checkFaking();

        $request->validate([
            'item'     => 'required|uuid',
            'type'     => [ 'required', new InEnum(new PaymentTypeEnum()) ],
            'price'    => 'required',
            'currency' => 'required',
            'last_4'   => 'required',
        ]);

        // Get payment item
        $item = $this->getItem($request);

        if (!isset($item)) {
            abort(400, 'Bad type or item');
        }

        // Validate Price
        if (!$item->verifyPrice($request->price)) {
            abort(400, 'Invalid Price');
        }

        // Create Card
        $user = Auth::user();
        $card = SegpayCard::create([
            'owner_type' => $user->getMorphString(),
            'owner_id'   => $user->getKey(),
            'token'      => 'fake',
            'nickname'   => $request->nickname ?? 'Fake Card',
            'card_type'  => $request->brand ?? '',
            'last_4'     => $request->last_4 ?? '0000',
        ]);

        // Create account for card
        $account = Account::create([
            'system' => 'segpay',
            'owner_type' => $user->getMorphString(),
            'owner_id' => $user->getKey(),
            'name' => $request->nickname ?? 'Fake Card',
            'type' => AccountTypeEnum::IN,
            'currency' => $request->currency,
            'resource_type' => $card->getMorphString(),
            'resource_id' => $card->getKey(),
        ]);
        $account->verified = true;
        $account->can_make_transactions = true;
        $account->save();

        // Dispatch Event
        FakeSegpayPayment::dispatch($item, $account, $request->type, $request->price);
    }

    /**
     *
     * @param Request $request
     * @return void
     */
    public function fakeConfirmation(Request $request)
    {
        $this->checkFaking();

        $request->validate([
            'item'     => 'required|uuid',
            'type'     => ['required', new InEnum(new PaymentTypeEnum())],
            'price'    => 'required',
            'currency' => 'required',
            'method'   => 'required|uuid',
        ]);

        // Get payment item
        $item = $this->getItem($request);
        if (!isset($item)) {
            abort(400, 'Bad type or item');
        }

        // Validate Price
        if (!$item->verifyPrice($request->price)) {
            abort(400, 'Invalid Price');
        }

        $account = Account::find($request->method);

        // Dispatch Event
        FakeSegpayPayment::dispatch($item, $account, $request->type, $request->price);
    }

    /**
     * Helper, gets the item associated with payment.
     *
     * @param mixed $request
     * @return null|Purchaseable|Tippable|Subscribable|void
     */
    private function getItem($request)
    {
        // Get payment item
        if ($request->type === PaymentTypeEnum::PURCHASE) {
            return PurchasableHelpers::getPurchasableItem($request->item);
        }
        if ($request->type === PaymentTypeEnum::TIP) {
            return TippableHelpers::getTippableItem($request->item);
        }
        if ($request->type === PaymentTypeEnum::SUBSCRIPTION) {
            return SubscribableHelpers::getSubscribableItem($request->item);
        }

        abort(400, 'Bad type or item');
    }

    /**
     * Checks if system is in fake mode and aborts if not.
     *
     * @return void
     * @throws HttpException
     * @throws NotFoundHttpException
     */
    private function checkFaking() {
        if (Config::get('app.env') === 'production' || Config::get('segpay.fake') === false) {
            abort(403);
        }
    }

}
