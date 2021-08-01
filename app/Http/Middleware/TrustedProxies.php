<?php

namespace App\Http\Middleware;

use Fideloper\Proxy\TrustProxies as Middleware;
use Illuminate\Http\Request;

/**
 * This middleware added to avoid conflicts with AWS load balancer removing https from routes
 * See: https://laravel.com/docs/8.x/requests#trusting-all-proxies
 *   and https://laravel.com/docs/8.x/requests#configuring-trusted-proxies
 *   for more information
 *
 * @package App\Http\Middleware
 */
class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * This is all due to us not knowing the exact ip of the load balancer all the time with aws.
     *
     * @var string|array
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO;
}
