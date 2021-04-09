<?php

return [
    'secure' => true,
    'mode' => env('SEGPAY_MODE', 'development'),
    'baseUrl' => env('SEGPAY_BASE_URL', 'secure2.segpay.com/billing/poset.cgi'),
    'baseOneClickUrl' => env('SEGPAY_BASE_ONE_CLICK_URL', 'secure2.segpay.com/billingOneClick.aspx'),
    'dynamicTransUrl' => env('SEGPAY_DYNAMIC_PRICE_URL', 'https://srs.segpay.com/PricingHash/PricingHash.svc/GetDynamicTrans'),
    'reportingServicesUrl' => env('SEGPAY_SRS_URL', 'srs.segpay.com'),
    'userId' => env('SEGPAY_USER_ID'),
    'accessKey' => env('SEGPAY_ACCESS_KEY'),
    'packageId' => env('SEGPAY_PACKAGE_ID', '199225'),
    'pricePointId' => env('SEGPAY_PRICE_POINT_ID', '26943'),
    'secret' => env('SEGPAY_SECRET_KEY', '95345D5827AEFC558525AD4878A46'),

    'paymentSessions' => [
        'baseUrl' => env('SEGPAY_PAYMENT_SESSIONS_BASE_URL', 'https://embedding.segpay.com/client/v1/payment-sessions/new'),
        'token' => env('SEGPAY_PAYMENT_SESSIONS_TOKEN', ''),
    ],
];
