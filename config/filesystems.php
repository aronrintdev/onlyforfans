<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. A "local" driver, as well as a variety of cloud
    | based drivers are available for your choosing. Just store away!
    |
    | Supported: "local", "ftp", "s3", "rackspace"
    |
    */

    'default' => 'local',


    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => 's3',

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    */

    'disks' => [

        'settings' => [
            'driver' => 'local',
            'root'   => public_path('uploads'),
            'url'    => env('APP_URL').'/uploads',
            'visibility' => 'public',
        ],

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app'),
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'visibility' => 'public',
        ],

        'uploads' => [
            'driver'     => 'local',
            'root'       => storage_path('uploads'),
            'visibility' => 'public',
        ],

        // Direct Talk to s3 bucket
        's3' => [
            'driver' => 's3',
            'key'    => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            // 'endpoint' => env('AWS_CDN_URL'),
            // 'url' => env('AWS_CDN_URL'),
            // 'bucket_endpoint' => env('AWS_BUCKET_ENDPOINT'),
            'visibility' => 'private',
        ],

        // CDN download paths
        'cdn' => [
            'driver' => 's3',
            'key'    => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_CDN_URL'),
            'url' => env('AWS_CDN_URL'),
            'bucket_endpoint' => env('AWS_BUCKET_ENDPOINT'),
            'visibility' => 'private',
        ],

    ],

    'useSigned' => env('AWS_USE_SIGNED', false),
    'useSignedCloudfront' => env('AWS_USE_SIGNED_CLOUDFRONT', false),
    /**
     * How long the temp signed urls will be available for
     */
    'availabilityMinutes' => env('CDN_AVAILABILITY_MINUTES', 5),

];
