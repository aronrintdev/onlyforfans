<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Tip;
use App\Rules\InEnum;
use GuzzleHttp\Client;
use App\Models\Campaign;
use App\Models\Timeline;
use Illuminate\Support\Str;
use App\Interfaces\Tippable;
use App\Rules\ValidCampaign;
use Illuminate\Http\Request;
use App\Enums\PaymentTypeEnum;
use App\Enums\CampaignTypeEnum;
use App\Interfaces\Purchaseable;
use App\Interfaces\Subscribable;
use App\Models\Financial\Account;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Models\Financial\SegpayWebsite;
use App\Models\Casts\Money as MoneyCast;
use App\Helpers\Tippable as TippableHelpers;
use App\Helpers\Purchasable as PurchasableHelpers;
use App\Helpers\Subscribable as SubscribableHelpers;

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


    public function generateOneClickSubscriptionPageUrl(Request $request)
    {
        $request->validate([
            'item' => 'required|uuid',
            'price' => 'required',
            'currency' => 'required',
            'method' => 'required|uuid',
        ]);
        Log::debug('SegPayController->generateOneClickSubscriptionPageUrl', [ 'request' => $request->all() ]);

        $item = SubscribableHelpers::getSubscribableItem($request->item);
        if (!isset($item)) {
            abort(400, 'Bad type or item');
        }

        $price = MoneyCast::toMoney($request->price, $request->currency);
        if (!$item->verifyPrice($price)) {
            abort(400, 'Invalid Price');
        }

        $account = Account::with('resource')->find($request->method);
        if (!isset($account) || !isset($account->resource)) {
            abort('400', 'Bad account');
        }

        $price = $item->formatMoneyDecimal($price);
        $period = 30;

        Log::debug('SegPayController->generateOneClickSubscriptionPageUrl', ['$price' => $price]);

        $client = new Client();
        $response = $client->request('GET', Config::get('segpay.dynamicTransUrl'), [
            'query' => ['value' => $price,],
        ]);

        $priceEncode = simplexml_load_string($response->getBody(), 'SimpleXMLElement', LIBXML_NOCDATA)->__toString();

        $client = new Client();
        $response = $client->request('GET', Config::get('segpay.dynamicRecurringUrl'), [
            'query' => [
                'MerchantID' => Config::get('segpay.merchantId'),
                'InitialAmount' => $price,
                'InitialLength' => $period,
                'RecurringAmount' => $price,
                'RecurringLength' => $period,
            ],
            'auth' => [Config::get('segpay.userId'), Config::get('segpay.accessKey') ],
        ]);

        $dynamicPricingId = Str::replace('"', '', $response->getBody()->__toString());

        $baseUrl = Config::get('segpay.secure') ? 'https://' : 'http://';
        $baseUrl .= Config::get('segpay.baseOneClickUrl');
        $packageId = Config::get('segpay.dynamicPackageId');
        $pricePointId = Config::get('segpay.dynamicPricePointId');
        $xEticketid = "{$packageId}:{$pricePointId}";

        $userId = Auth::user()->getKey();
        $description = urlencode(Config::get('segpay.description.subscription', 'All Fans Subscription'));
        $token = $account->resource->token;

        $url = "{$baseUrl}?x-eticketId={$xEticketid}&amount={$price}&dynamictrans={$priceEncode}&dynamicdesc={$description}&DynamicPricingID={$dynamicPricingId}&OCToken={$token}&user_id={$userId}&item_type=subscription&item_id={$item->id}";

        if ($item instanceof Timeline) {
            $whitesite = urlencode(SegpayWebsite::urlFor($item));
            $url .= "&whitesite={$whitesite}";
        }

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
            'campaign' => ['uuid', 'exists:campaigns,id', new ValidCampaign() ],
        ]);

        // Validate Payment allowed
        if (!$request->user()->canMakePayments()) {
            abort(403, 'Payments Forbidden');
        }

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
        if ($request->type !== PaymentTypeEnum::TIP) {
            if (!$item->verifyPrice($price)) {
                abort(400, 'Invalid Price');
            }
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
            if ($item instanceof Timeline) {
                $whitesite = SegpayWebsite::urlFor($item);
            }

            if ($request->has('campaign_id')) {
                $campaign = Campaign::find($request->campaign_id);
                if ($campaign->isValid()) {
                    if ($campaign->type === CampaignTypeEnum::DISCOUNT) {
                        $query['dynamicInitialAmount'] = $item->formatMoneyDecimal(
                            $campaign->getDiscountPrice($price)
                        );
                    }
                    if ($campaign->type === CampaignTypeEnum::TRIAL) {
                        $query['dynamicInitialAmount'] = '0';
                        $query['dynamicInitialDurationInDays'] = $campaign->trial_days;
                    }

                } else {
                    abort(400, 'campaign is not active');
                }
            }

        }

        $client = new Client();
        $response = $client->request('GET', Config::get('segpay.paymentSessions.baseUrl'), [
            'query' => $query,
        ]);

        $segpay = json_decode($response->getBody(), true);

        if (!isset($segpay['id'])) {
            Log::error('Unable to get Segments session id', [
                'query' => $query,
                'responseCode' => $response->getStatusCode(),
                'responseBody' => $response->getBody(),
            ]);
        }

        return [
            'id' => $segpay['id'],
            'pageId' => $segpay['pageId'],
            'expirationDateTime' => $segpay['expirationDateTime'],
            'packageId' => $packageId,
            'whitesite' => $whitesite ?? 'allfans.com',
        ];
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
            // return Tip::find($request->item);
            return TippableHelpers::getTippableItem($request->item);
        }
        if ($request->type === PaymentTypeEnum::SUBSCRIPTION) {
            return SubscribableHelpers::getSubscribableItem($request->item);
        }

        abort(400, 'Bad type or item');
    }
}
