<?php

return [
    'secure' => true,
    'mode' => env('SEGPAY_MODE', 'development'),

    'baseUrl' => env('SEGPAY_BASE_URL', 'secure2.segpay.com/billing/poset.cgi'),
    'baseOneClickUrl' => env('SEGPAY_BASE_ONE_CLICK_URL', 'secure2.segpay.com/billing/OneClick.aspx'),

    'baseOneClickDynamicUrl' => env('SEGPAY_BASE_ONE_CLICK_DYNAMIC_URL', 'https://service.segpay.com/OneClickSales.asmx/SalesServiceDynamic'),

    'dynamicTransUrl' => env('SEGPAY_DYNAMIC_PRICE_URL', 'https://srs.segpay.com/PricingHash/PricingHash.svc/GetDynamicTrans'),
    'dynamicRecurringUrl' => env('SEGPAY_DYNAMIC_RECURRING_URL', 'https://srs.segpay.com/MerchantServices/DynamicRecurring'),

    'reportingServicesUrl' => env('SEGPAY_SRS_URL', 'srs.segpay.com'),

    'userId' => env('SEGPAY_SRS_USER_ID'),
    'accessKey' => env('SEGPAY_SRS_ACCESS_KEY'),

    'merchantId' => env('SEGPAY_MERCHANT_ID', '21956'),

    'packageId' => env('SEGPAY_PACKAGE_ID', '199225'),
    'pricePointId' => env('SEGPAY_PRICE_POINT_ID', '26943'),

    'dynamicPackageId' => env('SEGPAY_DYNAMIC_PACKAGE_ID', '199373'),
    'dynamicPricePointId' => env('SEGPAY_DYNAMIC_PRICE_POINT_ID', '27232'),

    'secret' => env('SEGPAY_SECRET_KEY', '95345D5827AEFC558525AD4878A46'),

    'baseDynamicOneClickServiceUrl' => env('SEGPAY_BASE_DYNAMIC_ONE_CLICK_SERVICE_URL', 'https://service.segpay.com/OneClickSales.asmx/SalesServiceDynamic'),

    'paymentSessions' => [
        'baseUrl' => env('SEGPAY_PAYMENT_SESSIONS_BASE_URL', 'https://embedding.segpay.com/client/v1/payment-sessions/new'),
        'token' => env('SEGPAY_PAYMENT_SESSIONS_TOKEN', ''),
    ],

    'webhook' => [
        'username' => env('SEGPAY_WEBHOOK_USERNAME', ''),
        'password' => env('SEGPAY_WEBHOOK_PASSWORD', ''),
    ],

    /** Segpay Website API config */
    'websites' => [
        'endpoint' => env('SEGPAY_WEBSITES_ENDPOINT', 'https://srs.segpay.com/MerchantServices/merchant-urls'),
        'testMode' => env('SEGPAY_WEBSITES_TEST_MODE', env('APP_DEBUG', false)),
        'useSlug'  => env('SEGPAY_WEBSITES_USE_SLUG', false),

        'baseUrl'        => env('SEGPAY_WEBSITES_BASE_URL', 'allfans.com'),
        'baseApprovedId' => env('SEGPAY_WEBSITES_BASE_APPROVED_ID', 32898),
        'supportEmail'   => env('SEGPAY_WEBSITES_SUPPORT_EMAIL', 'support@allfans.com'),
        'techEmail'      => env('SEGPAY_WEBSITES_TECH_EMAIL'), // Leave null unless needed
        'faqLink'        => env('SEGPAY_WEBSITES_FAQ_LINK'),   // Leave null unless needed
        'helpLink'       => env('SEGPAY_WEBSITES_HELP_LINK'),  // Leave null unless needed
    ],

    /**
     * The Dynamic description to attach to a transaction
     */
    'description' => [
        'purchase'     => 'All Fans Purchase',
        'tip'          => 'All Fans Tip',
        'subscription' => 'All Fans Subscription',
    ],

    /**
     * Set to true to fake segpay functionality that needs ip whitelisting to work
     */
    'fake' => env('SEGPAY_FAKE', false),
];
