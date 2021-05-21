<?php

namespace App\Apis\Segpay;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Config;
use Psr\Http\Message\ResponseInterface;

/**
 * Api for SegPay Reporting Services (SRS) integration
 */
class Api
{
    /** Use https or not */
    protected $secure = true;
    /** Use development, testing, or production mode */
    protected $mode = 'development';
    /** BaseUrl to segpay api endpoint */
    protected $baseUrl = '';
    /** Base url of one click endpoint */
    protected $baseOneClickUrl = '';
    /** SegPay Reporting Services API Url */
    protected $reportingServicesUrl = '';
    /** Segpay UserId */
    protected $userId = '';
    /** Segpay AccessKey */
    protected $accessKey = '';

    protected $accessVariable = 'x-eticketid';

    /** Request headers to be sent on api call */
    public $requestHeaders = [];
    /** Url query arguments to be sent on api call */
    public $queryArguments = [];

    /**
     * Available Parameters from SegPay Documentation
     */
    public $availableParameters = [
        'x-eticketid' => [
            'packageId' => 'Package Id',
            'pricePointId' => 'Price Point Id',
        ],
        /**
         * Dynamic transactions parameters: https://www.sphelpdesk.com/kb/article/34-dynamic-pricing/
         */
        'amount' => 'Amount to charge',
        'dynamictrans' => 'Encrypted Hashed value from segpay`s SRS system',
        'dynamicdesc' => 'Url encoded description of the transaction',

        'OCToken' => 'One Click Token', // When CC is already known

        // Customization options
        'dmcurrency' => 'Display form in given currency',
        'paypagelanguage' => 'Pay Page Language',

        'x-billname' => 'Name',
        'x-billemail' => 'Email',
        'x-billaddr' => 'Address',
        'x-billcity' => 'City',
        'x-billstate' => 'State',
        'x-billzip' => 'Zip',
        'x-billcntry' => 'Country',

        'merchantpartnerid' => 'Merchant Partner Id',
        'x-auth-link' => 'Authorized Transaction Link',
        'x-auth-text' => 'Authorized Transaction Text',
        'x-decl-link' => 'Declined Transaction Link',
        'x-decl-text' => 'Declined Transaction Text',

        'username' => 'Consumers username',
        'password' => 'Consumers password', // Don't send on url param even though the documentation example does.
        'CrossSaleOff' => 'boolean, display as cross off sale',
        // '' => ''
    ];

    /** Available config options */
    protected $configOptions = [
        'secure',
        'mode',
        'baseUrl',
        'baseOneClickUrl',
        'reportingServicesUrl',
        'userId',
        'accessKey',
    ];
    /** Required config options */
    protected $requiredOptions = [
        'baseUrl',
        'baseOneClickUrl',
        'reportingServicesUrl',
        'userId',
        'accessKey',
    ];
    /** Base config path */
    protected $configPath = 'segpay';

    public function __construct(array $config = [])
    {
        $this->setupConfig($config);
    }


    /**
     * Get public url value for current setup
     */
    public function getUrl(): string
    {
        return $this->urlBuilder();
    }

    /**
     * Send Request
     */
    public function send($endpoint, $options = [])
    {
        $url = $this->urlBuilder($endpoint);
        $method = $options['method'] ?? 'GET';
        $query = $options['query'] ?? [];

        $client = new Client();
        return $client->request($method, $url, [ 'query' => array_merge([
            'UserID' => $this->userId,
            'UserAccessKey' => $this->accessKey,
        ], $query) ]);
    }

    /**
     * Cancel a subscription by purchaseId
     *
     * Possible Responses:
     * - Success
     * - Subscription not active, cancel failed
     * - PurchaseId/MerchantId mismatch
     * - Invalid Merchant
     * - Missing Parameter
     *
     * @param string $purchaseId
     * @param string|null $cancelReason
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public static function cancelSubscription($purchaseId, $cancelReason = null) {
        $api = new Api();
        return $api->send('ADM.asmx/CancelMembership', [ 'query' => [ 'PurchaseId' => $purchaseId, 'CancelReason' => $cancelReason ] ]);
    }

    /**
     * Create Url string
     */
    private function urlBuilder($endpoint = ''): string
    {
        $url = $this->secure ? 'https://' : 'http://';
        $url .= $this->baseUrl;
        $url .= '/' . $endpoint;
        return $url;
    }

    /**
     * Sets up base config
     */
    private function setupConfig($config): void
    {
        foreach($this->configOptions as $option) {
            if (!isset($config[$option])) {
                $config[$option] = Config::get( $this->configPath . '.' . $option);
            }
        }
        foreach($this->requiredOptions as $option) {
            if (!isset($config[$option])) {
                throw new Exception('Segpay Api: Required config ' . $option . ' is not set');
            }
        }
        foreach ($this->configOptions as $option) {
            if (isset($config[$option])) {
                $this->{$option} = $config[$option];
            }
        }
    }

}