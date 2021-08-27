<?php

return [


    /*
    |--------------------------------------------------------------------------
    | Default Queue Driver
    |--------------------------------------------------------------------------
    |
    | The Laravel queue API supports a variety of back-ends via an unified
    | API, giving you convenient access to each back-end using the same
    | syntax for each one. Here you may set the default queue driver.
    |
    | Supported: "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'default' => env('QUEUE_DRIVER', 'sync'),


    /**
     * Prefix to append to the beginning of queue names
     */
    'prefix' => env('QUEUE_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Laravel. You are free to add more.
    |
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        /**
         * Default database configuration
         */
        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => env('QUEUE_PREFIX', '') . 'default',
            'retry_after' => 90,
            'after_commit' => true,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => 'localhost',
            'queue' =>  env('QUEUE_PREFIX', '') . 'default',
            'retry_after' => 90,
        ],

        /**
         * Default sqs configuration
         */
        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('QUEUE_SQS_PATH'),
            'queue' => env('QUEUE_PREFIX', '') . 'default',
            'region' => env('QUEUE_SQS_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),
        ],

        /**
         * High Priority Queue
         */
        'high' => [
            'driver' => env('QUEUE_HIGH_DRIVER', 'database'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('QUEUE_SQS_PATH'),
            'queue' => env('QUEUE_PREFIX', '') . 'high',
            'region' => env('QUEUE_SQS_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),

            'table' => 'jobs',
            'after_commit' => true,
        ],

        /**
         * Low Priority Queue
         */
        'low' => [
            'driver' => env('QUEUE_LOW_DRIVER', 'database'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('QUEUE_SQS_PATH'),
            'queue' => env('QUEUE_PREFIX', '') . 'high',
            'region' => env('QUEUE_SQS_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),

            'table' => 'jobs',
            'after_commit' => true,
        ],

        /**
         * Queue jobs related to financial transactions, such as balance updates
         */
        'financial-transactions' => [
            'driver' => env('QUEUE_FINANCIAL_TRANSACTIONS_DRIVER', 'database'),
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('QUEUE_SQS_PATH'),
            'queue' => env('QUEUE_PREFIX', '') . 'financial-transactions',
            'region' => env('QUEUE_SQS_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),

            'table' => 'jobs',
            'after_commit' => true,
        ],

        /**
         * Queue jobs related to financial summaries, mainly creating new summaries
         * Note: has priority
         * - urgent
         * - high
         * - default
         */
        'financial-summaries' => [
            'driver' => env('QUEUE_FINANCIAL_SUMMARIES_DRIVER', 'database'),
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('QUEUE_SQS_PATH'),
            'queue' => env('QUEUE_PREFIX', '') . 'financial-summaries',
            'region' => env('QUEUE_SQS_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),

            'table' => 'jobs',
            'after_commit' => true,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' =>  env('QUEUE_PREFIX', '') . 'default',
            'retry_after' => 90,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control which database and table are used to store the jobs that
    | have failed. You may change them to any database / table you wish.
    |
    */

    'failed' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],

];
