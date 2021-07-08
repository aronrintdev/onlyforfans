<?php

namespace App\Models\Financial;

use Money\Money;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\Webhook;
use App\Models\Subscription;
use InvalidArgumentException;
use App\Enums\PaymentTypeEnum;
use App\Interfaces\Purchaseable;
use App\Interfaces\Tippable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Models\Casts\Money as MoneyCast;
use App\Models\Tip;
use App\Models\Traits\UsesUuid;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class SegpayCall extends Model
{
    use UsesUuid;

    protected $connection = 'financial';
    protected $table = 'segpay_calls';

    protected $forceCombV4Uuid = true;

    protected $guarded = [];

    protected $dates = [
        'sent_at',
        'processed_at',
        'failed_at',
    ];

    protected $casts = [
        'query_items'  => 'array',
        'params' => 'array',
    ];


    public function resource()
    {
        return $this->morphTo();
    }

    public function webhook()
    {
        return $this->hasOne(Webhook::class);
    }

    /* ------------------------------- Config ------------------------------- */
    #region Config
    public static function oneClickUrl(): string
    {
        return Config::get('segpay.baseDynamicOneClickServiceUrl');
    }

    #endregion Config

    /* ------------------------------- Scopes ------------------------------- */
    #region Scopes
    public function scopeProcessing($query)
    {
        return $query->whereNotNull('sent_at')->whereNull('processed_at')->whereNull('failed_at');
    }


    #endregion Scopes

    /* ------------------------------ Functions ----------------------------- */
    #region Functions

    public static function confirmPurchase(Account $account, Money $price, Purchaseable $purchasable)
    {
        // Verify not already processing this subscription
        if (static::where('resource_id', $purchasable->getKey())->processing()->count() > 0) {
            return false;
        }

        $segpayCall = static::create([
            'url' => static::oneClickUrl(),
            'method' => 'GET',
            'amount' => $price->getAmount(),
            'currency' => $price->getCurrency()->getCode(),
            'user_id' => Auth::user()->getKey(),
            'account_id' => $account->getKey(),
            'resource_type' => $purchasable->getMorphString(),
            'resource_id' => $purchasable->getKey(),
        ]);

        $packageId = Config::get('segpay.packageId');
        $pricePointId = Config::get('segpay.pricePointId');

        $segpayCall->query_items = [
            'eticketid' => "{$packageId}:{$pricePointId}",
            'amount' => MoneyCast::formatMoneyDecimal($price),
            'currencyCode' => $price->getCurrency()->getCode(),
            'dynamicdesc' => urlencode(Config::get('segpay.description.purchase')),
            'OCToken' => $account->resource->token,
            'item_type' => PaymentTypeEnum::PURCHASE,
            'item_id' => $purchasable->getKey(),
            'user_id' => Auth::user()->getKey(),
            'reference_id' => $segpayCall->getKey(),
        ];
        $segpayCall->save();

        return $segpayCall->send();
    }

    public static function confirmTip(Account $account, Money $price, Tip $tip)
    {
        // Verify not already processing this subscription
        if (static::where('resource_id', $tip->getKey())->processing()->count() > 0) {
            return false;
        }

        $segpayCall = static::create([
            'url' => static::oneClickUrl(),
            'method' => 'GET',
            'amount' => $price->getAmount(),
            'currency' => $price->getCurrency()->getCode(),
            'user_id' => Auth::user()->getKey(),
            'account_id' => $account->getKey(),
            'resource_type' => $tip->getMorphString(),
            'resource_id' => $tip->getKey(),
        ]);

        $packageId = Config::get('segpay.packageId');
        $pricePointId = Config::get('segpay.pricePointId');

        $segpayCall->query_items = [
            'eticketid' => "{$packageId}:{$pricePointId}",
            'amount' => MoneyCast::formatMoneyDecimal($price),
            'currencyCode' => $price->getCurrency()->getCode(),
            'dynamicdesc' => urlencode(Config::get('segpay.description.tip')),
            'OCToken' => $account->resource->token,
            'item_type' => PaymentTypeEnum::TIP,
            'item_id' => $tip->getKey(),
            'user_id' => Auth::user()->getKey(),
            'reference_id' => $segpayCall->getKey(),
        ];
        $segpayCall->save();

        return $segpayCall->send();
    }

    public static function confirmSubscription(Account $account, Money $price, Subscription $subscription )
    {
        // Verify not already processing this subscription
        if (static::where('resource_id', $subscription->getKey())->processing()->count() > 0) {
            return false;
        }

        $segpayCall = static::create([
            'url' => static::oneClickUrl(),
            'method' => 'GET',
            'amount' => $price->getAmount(),
            'currency' => $price->getCurrency()->getCode(),
            'user_id' => Auth::user()->getKey(),
            'account_id' => $account->getKey(),
            'resource_type' => $subscription->getMorphString(),
            'resource_id' => $subscription->getKey(),
        ]);

        $packageId = Config::get('segpay.dynamicPackageId');
        $pricePointId = Config::get('segpay.dynamicPricePointId');

        $segpayCall->query_items = [
            'eticketid' => "{$packageId}:{$pricePointId}",
            'amount' => MoneyCast::formatMoneyDecimal($price),
            'dynamicdesc' => urlencode(Config::get('segpay.description.subscription')),
            'dynamicInitialDurationInDays'   => 30,
            'dynamicRecurringAmount'         => MoneyCast::formatMoneyDecimal($price),
            'dynamicRecurringDurationInDays' => 30,
            'OCToken' => $account->resource->token,
            'type' => PaymentTypeEnum::SUBSCRIPTION,
            'item_id' => $subscription->getKey(),
            'user_id' => Auth::user()->getKey(),
            'reference_id' => $segpayCall->getKey(),
        ];
        $segpayCall->save();

        return $segpayCall->send();
    }

    /**
     * Sends call to Segpay
     *
     * @return void
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function send()
    {
        $this->sent_at = Carbon::now();
        $this->save();
        $client = new Client();
        $response = $client->request($this->method, $this->url, [
            'query' => $this->query_items,
        ]);

        Log::debug('SegpayCall Response', [ 'code' => $response->getStatusCode(), 'body' => $response->getBody() ]);

        $this->response = "Code: {$response->getStatusCode()}" . " Body: {$response->getBody()}";
        if ($response->getStatusCode() >= 300) {
            $this->failed_at = Carbon::now();
        }

        $this->processed_at = Carbon::now();
        $this->save();
        return $this;
    }

    #endregion Functions

}