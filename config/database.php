<?php

return [

    'modes' => [ 'ONLY_FULL_GROUP_BY','STRICT_TRANS_TABLES','NO_ZERO_IN_DATE','NO_ZERO_DATE','ERROR_FOR_DIVISION_BY_ZERO','NO_AUTO_CREATE_USER','NO_ENGINE_SUBSTITUTION' ],

    /*
    |--------------------------------------------------------------------------
    | PDO Fetch Style
    |--------------------------------------------------------------------------
    |
    | By default, database results will be returned as instances of the PHP
    | stdClass object; however, you may desire to retrieve records in an
    | array format for simplicity. Here you can tweak the fetch style.
    |
    */

    'fetch' => PDO::FETCH_OBJ,


    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'primary'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'primary' => [
            'driver' => env('DB_DRIVER', 'mysql'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'financial' => [
            'driver' => env('DB_FINANCIAL_DRIVER', 'mysql'),
            'host' => env('DB_FINANCIAL_HOST', 'localhost'),
            'port' => env('DB_FINANCIAL_PORT', '3306'),
            'database' => env('DB_FINANCIAL_DATABASE', 'forge'),
            'username' => env('DB_FINANCIAL_USERNAME', 'forge'),
            'password' => env('DB_FINANCIAL_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => env('DB_FINANCIAL_PREFIX', ''),
            'strict' => false,
            'engine' => null,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        // testing DB setup
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],

        // ref: https://chrisduell.com/speeding-up-unit-tests-in-php
        // $ php artisan migrate:refresh --seed --database="template" --env="testing"
        // For test migration only (sqlite file)
        'template' => [
            'driver' => 'sqlite',
            'database' => __DIR__.'/../database/template.sqlite',
            'prefix' => ''
        ],
        'templateEmpty' => [
            'driver' => 'sqlite',
            'database' => __DIR__.'/../database/templateEmpty.sqlite',
            'prefix' => '',
        ],
        /*
        'testing' => [
            'driver' => 'sqlite',
            'database' => __DIR__.'/../database/tmp4test.sqlite',
            'prefix' => ''
        ],
         */

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'cluster' => false,

        'default' => [
            'host' => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

    ],


    // UUID Settings
    'uuid' => [
        'useTrueRandom' => env('DB_UUID_USE_TRUE_RANDOM', false),
    ],

];
