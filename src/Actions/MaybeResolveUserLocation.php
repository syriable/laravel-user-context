<?php

declare(strict_types=1);

namespace Syriable\UserContext\Actions;

use Illuminate\Database\Eloquent\Model;
use Syriable\UserContext\Jobs\ResolveUserLocation;
use Syriable\UserContext\Support\IpAddress;
use Syriable\UserContext\Support\PackageCache;

/**
 * Dispatches a geolocation lookup only when the user's IP is new or has
 * changed since the last resolved lookup — a cached per-user fingerprint
 * keeps repeat requests free.
 *
 * @internal
 */
final readonly class MaybeResolveUserLocation
{
    public function __invoke(Model $user, ?string $ip): void
    {
        if ($ip === null || ! (bool) config('user-context.geolocation.enabled', true)) {
            return;
        }

        if (! IpAddress::isValid($ip)) {
            return;
        }

        if ((bool) config('user-context.ip.skip_private', true) && IpAddress::isPrivate($ip)) {
            return;
        }

        $key = sprintf('user-context:geo-marker:%s:%s', $user->getMorphClass(), $user->getKey());
        $fingerprint = sha1($ip);
        $cache = PackageCache::store();

        if ($cache->get($key) === $fingerprint) {
            return;
        }

        $cache->put($key, $fingerprint, (int) config('user-context.geolocation.cache_ttl', 604800));

        ResolveUserLocation::dispatchUsingConfig($user, $ip);
    }
}
