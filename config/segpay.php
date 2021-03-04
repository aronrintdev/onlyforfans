<?php

return [
    'secure' => true,
    'mode' => env('SEGPAY_MODE', 'development'),
    'baseUrl' => env('SEGPAY_BASE_URL', 'secure2.segpay.com/billing/poset.cgi'),
    'baseOneClickUrl' => env('SEGPAY_BASE_ONE_CLICK_URL', 'secure2.segpay.com/billingOneClick.aspx'),
    'userId' => env('SEGPAY_USER_ID'),
    'accessKey' => env('SEGPAY_ACCESS_KEY'),
];
