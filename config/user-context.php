<?php

declare(strict_types=1);

use Syriable\UserContext\Models\LoginRecord;
use Syriable\UserContext\Models\UserContext;

return [

    /*
    |--------------------------------------------------------------------------
    | Online timeout (seconds)
    |--------------------------------------------------------------------------
    | A user is considered online when their last activity happened within
    | this window. Presence is always computed from `last_seen_at`, so this
    | value can be changed at any time without touching stored data.
    */
    'online_timeout' => 300,

    /*
    |--------------------------------------------------------------------------
    | Activity throttle (seconds)
    |--------------------------------------------------------------------------
    | `last_seen_at` is written at most once per throttle window per user,
    | keeping the tracking middleware close to free on busy applications.
    | Must be lower than `online_timeout`.
    */
    'activity_throttle' => 60,

    /*
    |--------------------------------------------------------------------------
    | Cache store
    |--------------------------------------------------------------------------
    | The cache store used for activity throttling and geolocation caching.
    | `null` uses your application's default store.
    */
    'cache_store' => null,

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    | Swap these for your own models to add relationships or behavior. Your
    | models should extend the package models they replace.
    */
    'models' => [
        'context' => UserContext::class,
        'login_record' => LoginRecord::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    | The package can register a heartbeat endpoint (used by the
    | <x-user-context::heartbeat /> component and SPA clients) and a `/me`
    | endpoint returning the authenticated user's context as JSON.
    */
    'routes' => [
        'enabled' => true,
        'prefix' => 'user-context',
        'middleware' => ['web', 'auth'],
        'expose_me' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Heartbeat
    |--------------------------------------------------------------------------
    | `interval` is the number of seconds between pings sent by the bundled
    | JavaScript heartbeat component. Keep it below `online_timeout`.
    */
    'heartbeat' => [
        'enabled' => true,
        'interval' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | IP geolocation
    |--------------------------------------------------------------------------
    | Supported drivers: "ipapi" (free, no key), "ipinfo" (token),
    | "maxmind" (local GeoLite2 database, requires geoip2/geoip2),
    | and "null" (disables lookups). Register custom drivers with
    | UserContext::extendGeolocation(). Successful lookups are cached
    | for `cache_ttl` seconds to avoid repeated external calls.
    */
    'geolocation' => [
        'enabled' => true,
        'driver' => 'ipapi',
        'cache_ttl' => 60 * 60 * 24 * 7,
        'timeout' => 2,
        'drivers' => [
            'ipinfo' => [
                'token' => env('IPINFO_TOKEN'),
            ],
            'maxmind' => [
                'database' => env('MAXMIND_GEOLITE2_DB'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    | Geolocation lookups run as a job. By default the job is executed
    | synchronously (no queue worker required). Enable this to push lookups
    | onto a queue instead.
    */
    'queue' => [
        'enabled' => false,
        'connection' => null,
        'queue' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | IP privacy
    |--------------------------------------------------------------------------
    | How IP addresses are persisted: "store" keeps the raw address,
    | "anonymize" zeroes the host part (IPv4 /24, IPv6 /48), "hash" stores
    | an HMAC-SHA256 of the address keyed by your app key, and "discard"
    | never persists the address (geolocation still works — the raw IP is
    | only used in memory for the lookup). `skip_private` skips geolocation
    | for private/reserved ranges such as 127.0.0.1 or 10.0.0.0/8.
    */
    'ip' => [
        'privacy' => 'store',
        'skip_private' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Time
    |--------------------------------------------------------------------------
    | `day_periods` maps each period to the hour (0-23) it starts at, in the
    | user's local timezone. `convenient_hours` bounds the window used by
    | TimeComparison::isConvenientTime() — "is now a reasonable local time
    | to contact this user".
    */
    'time' => [
        'day_periods' => [
            'morning' => 5,
            'afternoon' => 12,
            'evening' => 17,
            'night' => 21,
        ],
        'convenient_hours' => [
            'from' => 8,
            'until' => 21,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Locale detection
    |--------------------------------------------------------------------------
    | When enabled, the tracking middleware derives the user's locale from
    | the Accept-Language header (unless the user has set an explicit
    | preference). Leave `supported` empty to accept any locale, or list
    | your application's locales to restrict matching.
    */
    'locale' => [
        'detect_from_header' => true,
        'supported' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Login history
    |--------------------------------------------------------------------------
    | An append-only ledger of logins (one row per login). Prune it with
    | the `user-context:prune` command.
    */
    'login_history' => [
        'enabled' => true,
        'retention_days' => 365,
    ],

    /*
    |--------------------------------------------------------------------------
    | Agent
    |--------------------------------------------------------------------------
    | When enabled, the raw User-Agent string is stored on the context row
    | (and on login records). Disabled by default to minimize stored data.
    */
    'agent' => [
        'collect' => false,
    ],

];
