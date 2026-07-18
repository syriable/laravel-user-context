<?php

declare(strict_types=1);

namespace Syriable\UserContext\Geolocation\Drivers;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Syriable\UserContext\Contracts\GeolocationProvider;
use Syriable\UserContext\Data\LocationData;
use Syriable\UserContext\Exceptions\GeolocationException;
use Throwable;

/**
 * Local MaxMind GeoLite2/GeoIP2 City database driver. No network calls —
 * ideal for shared hosting and high volume. Requires the optional
 * geoip2/geoip2 package and a downloaded .mmdb file.
 */
final class MaxMindDriver implements GeolocationProvider
{
    private ?Reader $reader = null;

    public function __construct(private readonly ?string $database) {}

    public function locate(string $ip): ?LocationData
    {
        try {
            $record = $this->reader()->city($ip);
        } catch (AddressNotFoundException) {
            return null;
        } catch (GeolocationException $exception) {
            throw $exception;
        } catch (Throwable) {
            return null;
        }

        return new LocationData(
            ip: $ip,
            countryCode: $record->country->isoCode,
            region: $record->mostSpecificSubdivision->name,
            city: $record->city->name,
            timezone: $record->location->timeZone,
        );
    }

    private function reader(): Reader
    {
        if ($this->reader instanceof Reader) {
            return $this->reader;
        }

        if (! class_exists(Reader::class)) {
            throw GeolocationException::missingMaxMindDependency();
        }

        if ($this->database === null || ! is_file($this->database)) {
            throw GeolocationException::missingMaxMindDatabase($this->database);
        }

        return $this->reader = new Reader($this->database);
    }
}
