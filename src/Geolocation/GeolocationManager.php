<?php

declare(strict_types=1);

namespace Syriable\UserContext\Geolocation;

use Illuminate\Support\Manager;
use Syriable\UserContext\Contracts\GeolocationProvider;
use Syriable\UserContext\Geolocation\Drivers\IpApiDriver;
use Syriable\UserContext\Geolocation\Drivers\IpInfoDriver;
use Syriable\UserContext\Geolocation\Drivers\MaxMindDriver;
use Syriable\UserContext\Geolocation\Drivers\NullDriver;

/**
 * Driver manager for geolocation providers. Register custom providers
 * with extend():
 *
 *     app(GeolocationManager::class)->extend('acme', fn () => new AcmeProvider());
 *
 * @method GeolocationProvider driver(string|null $driver = null)
 */
final class GeolocationManager extends Manager
{
    public function getDefaultDriver(): string
    {
        $driver = $this->config->get('user-context.geolocation.driver', 'ipapi');

        return is_string($driver) ? $driver : 'ipapi';
    }

    protected function createIpapiDriver(): GeolocationProvider
    {
        return new IpApiDriver(timeout: $this->timeout());
    }

    protected function createIpinfoDriver(): GeolocationProvider
    {
        $token = $this->config->get('user-context.geolocation.drivers.ipinfo.token');

        return new IpInfoDriver(
            token: is_string($token) && $token !== '' ? $token : null,
            timeout: $this->timeout(),
        );
    }

    protected function createMaxmindDriver(): GeolocationProvider
    {
        $database = $this->config->get('user-context.geolocation.drivers.maxmind.database');

        return new MaxMindDriver(database: is_string($database) ? $database : null);
    }

    protected function createNullDriver(): GeolocationProvider
    {
        return new NullDriver;
    }

    private function timeout(): int
    {
        return (int) $this->config->get('user-context.geolocation.timeout', 2);
    }
}
