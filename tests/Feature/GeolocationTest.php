<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Syriable\UserContext\Actions\UpdateUserLocation;
use Syriable\UserContext\Contracts\GeolocationProvider;
use Syriable\UserContext\Data\LocationData;
use Syriable\UserContext\Events\UserLocationUpdated;
use Syriable\UserContext\Events\UserTimezoneChanged;
use Syriable\UserContext\Facades\UserContext;
use Syriable\UserContext\Geolocation\CachingGeolocator;
use Syriable\UserContext\Jobs\ResolveUserLocation;

describe('applying a geolocation result', function (): void {
    it('writes location and timezone onto the user context', function (): void {
        $user = makeUser();

        app(UpdateUserLocation::class)($user, new LocationData(
            ip: '8.8.8.8',
            countryCode: 'US',
            region: 'California',
            city: 'Mountain View',
            timezone: 'America/Los_Angeles',
        ));

        $context = UserContext::contextFor($user->fresh());

        expect($context->country_code)->toBe('US')
            ->and($context->city)->toBe('Mountain View')
            ->and($context->timezone)->toBe('America/Los_Angeles')
            ->and($user->location()->countryName())->toBe('United States');
    });

    it('fires UserLocationUpdated and UserTimezoneChanged', function (): void {
        Event::fake([UserLocationUpdated::class, UserTimezoneChanged::class]);
        $user = makeUser();

        app(UpdateUserLocation::class)($user, new LocationData(
            ip: '8.8.8.8',
            countryCode: 'US',
            timezone: 'America/New_York',
        ));

        Event::assertDispatched(UserLocationUpdated::class);
        Event::assertDispatched(UserTimezoneChanged::class);
    });

    it('never overrides a user-chosen timezone with an IP-detected one', function (): void {
        $user = makeUser();
        UserContext::overrideTimezone($user, 'Europe/Berlin');

        app(UpdateUserLocation::class)($user, new LocationData(
            ip: '8.8.8.8',
            countryCode: 'US',
            timezone: 'America/New_York',
        ));

        expect(UserContext::contextFor($user->fresh())->timezone)->toBe('Europe/Berlin');
    });

    it('honors the configured IP privacy mode when storing the address', function (): void {
        config()->set('user-context.ip.privacy', 'anonymize');
        $user = makeUser();

        app(UpdateUserLocation::class)($user, new LocationData(ip: '203.0.113.42', countryCode: 'US'));

        expect(UserContext::contextFor($user->fresh())->ip_address)->toBe('203.0.113.0');
    });
});

describe('the caching geolocator', function (): void {
    it('only calls the underlying provider once per address', function (): void {
        $calls = 0;
        $inner = new class($calls) implements GeolocationProvider
        {
            public function __construct(public int &$calls) {}

            public function locate(string $ip): ?LocationData
            {
                $this->calls++;

                return new LocationData(ip: $ip, countryCode: 'US');
            }
        };

        $caching = new CachingGeolocator($inner, cache()->store('array'), 3600);

        $caching->locate('8.8.8.8');
        $caching->locate('8.8.8.8');

        expect($calls)->toBe(1);
    });
});

describe('the resolve-location job', function (): void {
    it('resolves an address through the bound provider and stores it', function (): void {
        $user = makeUser();

        app()->bind(GeolocationProvider::class, fn (): GeolocationProvider => new class implements GeolocationProvider
        {
            public function locate(string $ip): ?LocationData
            {
                return new LocationData(ip: $ip, countryCode: 'JP', timezone: 'Asia/Tokyo');
            }
        });

        dispatch_sync(new ResolveUserLocation($user, '133.11.0.1'));

        expect(UserContext::contextFor($user->fresh())->timezone)->toBe('Asia/Tokyo');
    });
});
