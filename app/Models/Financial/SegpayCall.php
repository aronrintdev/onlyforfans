<?php

namespace App\Models\Financial;

use Money\Money;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\Webhook;
use App\Models\Subscription;
use InvalidArgumentException;
use App\Enums\PaymentTypeEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Models\Casts\Money as MoneyCast;
use App\Models\Traits\UsesUuid;
use GuzzleHttp\Exception\GuzzleException;

class SegpayCall extends Model
{
    use UsesUuid;

    protected $table = 'segpay_calls';

    protected $forceCombV4Uuid = true;

    protected $dates = [
        'sent_at',
        'processed_at',
        'failed_at',
    ];

    protected $casts = [
        'query'  => 'array',
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
    public static function onClickUrl(): string
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

    public static function confirmSubscription(Account $account, Money $price, Subscription $subscription )
    {
        // Verify not already processing this subscription
        if (static::where('resource_id', $subscription->getKey())->processing()->count() > 0) {
            return false;
        }

        $segpayCall = static::create([
            'url' => static::onClickUrl(),
            'method' => 'GET',
            'amount' => $price->getAmount(),
            'currency' => $price->getCurrency()->getCode(),
            'user_id' => Auth::user()->getKey(),
            'account_id' => $account->getKey(),
        ])->resource()->save($subscription);

        $packageId = Config::get('segpay.packageId');
        $pricePointId = Config::get('segpay.pricePointId');

        $segpayCall->query = [
            'eticketid' => "{$packageId}:{$pricePointId}",
            'amount' => MoneyCast::formatMoneyDecimal($price),
            'dynamicdesc' => urlencode('All Fans Subscription'),
            'OCToken' => $account->resource->token,
            'type' => PaymentTypeEnum::SUBSCRIPTION,
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
        $client = new Client();
        $response = $client->request($this->method, $this->url, [
            'query' => $this->query,
        ]);

        $this->response = "Code: {$response->getStatusCode}" . " Body: {$response->getBody()}";
        if ($response->getStatusCode <= 300) {
            $this->failed_at = Carbon::now();
        }

        $this->save();
        return;
    }

    #endregion Functions

}