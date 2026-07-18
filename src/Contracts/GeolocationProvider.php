<?php

declare(strict_types=1);

namespace Syriable\UserContext\Contracts;

use Syriable\UserContext\Data\LocationData;

interface GeolocationProvider
{
    /**
     * Resolve an IP address to a location, or null when the address
     * cannot be resolved. Implementations must never throw for a plain
     * lookup miss — reserve exceptions for misconfiguration.
     */
    public function locate(string $ip): ?LocationData;
}
