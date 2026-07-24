<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseConfig
{
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'throttle'      => \App\Filters\ThrottleFilter::class,
        'maintenance'   => \App\Filters\MaintenanceFilter::class,
    ];

    public array $globals = [
        'before' => ['maintenance'],
        'after'  => ['toolbar'],
    ];

    /**
     * Apply CSRF to POST requests, but EXCLUDE all API routes
     */
//    public array $methods = [
//        'post' => [
//            'csrf' => [
//                'except' => [
//                    'api/*',
//                    'api/v1/*',
//                    'api/v2/*',
//                ]
//            ]
//        ]
//    ];

    /**
     * No need to define throttle here — it's applied via route group
     */
//    public array $filters = [];

//    public array $filters = [
//        'csrf' => [
//            'before' => ['*'],
//            'except' => [
//                'api/*',
//                'api/v1/*',
//                'api/v2/*',
//            ]
//        ],
//    ];

    /**
     * ❌ REMOVE CSRF from methods
     */
    public array $methods = [];

    /**
     * ❌ REMOVE global CSRF filters
     */
    public array $filters = [];
}