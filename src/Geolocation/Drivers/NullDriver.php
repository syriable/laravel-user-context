<?php

declare(strict_types=1);

namespace Syriable\UserContext\Geolocation\Drivers;

use Syriable\UserContext\Contracts\GeolocationProvider;
use Syriable\UserContext\Data\LocationData;

/**
 * Resolves nothing. Use it to disable geolocation entirely while keeping
 * the rest of the package active.
 */
final readonly class NullDriver implements GeolocationProvider
{
    public function locate(string $ip): ?LocationData
    {
        return null;
    }
}
