<?php

/**
 * Configuration for collections
 */

return [
    /**
     * Max allowed pagination take per request for a resource
     */
    'max' => [
        'posts' => env('MAX_POSTS_PER_REQUEST', 10),
        'subscriptions' => env('MAX_SUBSCRIPTIONS_PER_REQUEST', 50),
        'transactions' => env('MAX_TRANSACTIONS_PER_REQUEST', 20),
    ],
];
