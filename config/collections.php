<?php

/**
 * Configuration for collections
 */
$default = env('MAX_DEFAULT_PER_REQUEST', 10);
$small   = env('MAX_SMALL_PER_REQUEST', 10);
$mid     = env('MAX_MID_PER_REQUEST', 20);
$large   = env('MAX_LARGE_PER_REQUEST', 50);

return [
    /**
     * Max allowed pagination take per request for a resource
     */
    'defaultMax' => $default,
    'size' => [
        'default' => $default,
        'small'   => $small,
        'mid'     => $mid,
        'large'   => $large,
    ],
    'max' => [
        'accounts'      => env('MAX_ACCOUNTS_PER_REQUEST' , $mid  ),
        'ach_accounts'  => env('MAX_ACH_ACCOUNTS_PER_REQUEST' , $mid  ),
        'posts'         => env('MAX_POSTS_PER_REQUEST'        , $small),
        'subscriptions' => env('MAX_SUBSCRIPTIONS_PER_REQUEST', $large),
        'transactions'  => env('MAX_TRANSACTIONS_PER_REQUEST' , $mid  ),
    ],
];
