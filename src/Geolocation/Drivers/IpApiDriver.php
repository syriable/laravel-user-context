<?php

declare(strict_types=1);

namespace Syriable\UserContext\Geolocation\Drivers;

use Illuminate\Support\Facades\Http;
use Syriable\UserContext\Contracts\GeolocationProvider;
use Syriable\UserContext\Data\LocationData;
use Throwable;

/**
 * Free ip-api.com driver. No API key required; the free tier only serves
 * plain HTTP and is limited to ~45 requests per minute — lookups are
 * cached upstream to stay well below that.
 */
final readonly class IpApiDriver implements GeolocationProvider
{
    public function __construct(private int $timeout = 2) {}

    public function locate(string $ip): ?LocationData
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("http://ip-api.com/json/{$ip}", [
                    'fields' => 'status,countryCode,regionName,city,timezone',
                ]);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful() || $response->json('status') !== 'success') {
            return null;
        }

        return new LocationData(
            ip: $ip,
            countryCode: $this->stringOrNull($response->json('countryCode')),
            region: $this->stringOrNull($response->json('regionName')),
            city: $this->stringOrNull($response->json('city')),
            timezone: $this->stringOrNull($response->json('timezone')),
        );
    }

    private function stringOrNull(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
