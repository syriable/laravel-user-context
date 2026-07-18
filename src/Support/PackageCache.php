<?php

declare(strict_types=1);

namespace Syriable\UserContext\Support;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

final class PackageCache
{
    /**
     * The cache repository configured for the package
     * (`user-context.cache_store`, defaulting to the app's default store).
     */
    public static function store(): Repository
    {
        $store = config('user-context.cache_store');

        return Cache::store(is_string($store) ? $store : null);
    }
}
