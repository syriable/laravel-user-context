<?php

declare(strict_types=1);

namespace Syriable\UserContext\Exceptions;

use RuntimeException;

final class GeolocationException extends RuntimeException
{
    public static function missingMaxMindDependency(): self
    {
        return new self(
            'The MaxMind geolocation driver requires the geoip2/geoip2 package. Install it with: composer require geoip2/geoip2',
        );
    }

    public static function missingMaxMindDatabase(?string $path): self
    {
        return new self(sprintf(
            'The MaxMind GeoLite2 database was not found at [%s]. Set user-context.geolocation.drivers.maxmind.database to a valid .mmdb path.',
            $path ?? 'null',
        ));
    }
}
