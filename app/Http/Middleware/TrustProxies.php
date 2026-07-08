<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB |
        Request::HEADER_X_FORWARDED_PREFIX;

    /**
     * Create a new trust proxies instance.
     */
    public function __construct()
    {
        //  Pour Laravel Cloud / production
        if (app()->environment('production')) {
            $this->proxies = [
                '103.21.244.0/22',  // Cloudflare
                '103.22.200.0/22',  // Cloudflare
                '103.31.4.0/22',    // Cloudflare
                '104.16.0.0/13',    // Cloudflare
                '104.24.0.0/14',    // Cloudflare
                '108.162.192.0/18', // Cloudflare
                '131.0.72.0/22',    // Cloudflare
                '141.101.64.0/18',  // Cloudflare
                '162.158.0.0/15',   // Cloudflare
                '172.64.0.0/13',    // Cloudflare
                '173.245.48.0/20',  // Cloudflare
                '188.114.96.0/20',  // Cloudflare
                '190.93.240.0/20',  // Cloudflare
                '197.234.240.0/22', // Cloudflare
                '198.41.128.0/17',  // Cloudflare
                // Ajouter les IP de Laravel Cloud ici
            ];
        }
    }
}