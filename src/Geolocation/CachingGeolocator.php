<?php

declare(strict_types=1);

namespace Syriable\UserContext\Geolocation;

use Illuminate\Contracts\Cache\Repository;
use Syriable\UserContext\Contracts\GeolocationProvider;
use Syriable\UserContext\Data\LocationData;

/**
 * Caching decorator around any geolocation provider. Successful lookups
 * are cached so the same IP never hits the external provider twice within
 * the configured TTL.
 */
final readonly class CachingGeolocator implements GeolocationProvider
{
    public function __construct(
        private GeolocationProvider $provider,
        private Repository $cache,
        private int $ttl,
    ) {}

    public function locate(string $ip): ?LocationData
    {
        $key = 'user-context:geo:'.sha1($ip);

        $cached = $this->cache->get($key);

        if (is_array($cached) && isset($cached['ip']) && is_string($cached['ip'])) {
            /** @var array{ip: string, country_code?: string|null, region?: string|null, city?: string|null, timezone?: string|null} $cached */
            return LocationData::fromArray($cached);
        }

        $location = $this->provider->locate($ip);

        if ($location instanceof LocationData) {
            $this->cache->put($key, $location->toArray(), $this->ttl);
        }

        return $location;
    }
}
