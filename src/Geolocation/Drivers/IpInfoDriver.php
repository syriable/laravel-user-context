<?php

declare(strict_types=1);

namespace Syriable\UserContext\Geolocation\Drivers;

use Illuminate\Support\Facades\Http;
use Syriable\UserContext\Contracts\GeolocationProvider;
use Syriable\UserContext\Data\LocationData;
use Throwable;

/**
 * ipinfo.io driver. Works without a token on the small free tier;
 * set `user-context.geolocation.drivers.ipinfo.token` for real quotas.
 */
final readonly class IpInfoDriver implements GeolocationProvider
{
    public function __construct(
        private ?string $token = null,
        private int $timeout = 2,
    ) {}

    public function locate(string $ip): ?LocationData
    {
        $query = $this->token !== null ? ['token' => $this->token] : [];

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get("https://ipinfo.io/{$ip}/json", $query);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful() || $response->json('bogon') === true) {
            return null;
        }

        return new LocationData(
            ip: $ip,
            countryCode: $this->stringOrNull($response->json('country')),
            region: $this->stringOrNull($response->json('region')),
            city: $this->stringOrNull($response->json('city')),
            timezone: $this->stringOrNull($response->json('timezone')),
        );
    }

    private function stringOrNull(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
