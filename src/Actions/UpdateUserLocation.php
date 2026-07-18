<?php

declare(strict_types=1);

namespace Syriable\UserContext\Actions;

use Illuminate\Database\Eloquent\Model;
use Syriable\UserContext\Data\LocationData;
use Syriable\UserContext\Enums\ContextSource;
use Syriable\UserContext\Enums\IpPrivacyMode;
use Syriable\UserContext\Events\UserLocationUpdated;
use Syriable\UserContext\Events\UserTimezoneChanged;
use Syriable\UserContext\UserContextManager;

/**
 * Applies a geolocation result to the user's context row. The IP address
 * is persisted through the configured privacy mode; an IP-detected
 * timezone never overwrites a user-chosen one.
 *
 * @internal
 */
final readonly class UpdateUserLocation
{
    public function __construct(private UserContextManager $manager) {}

    public function __invoke(Model $user, LocationData $location): void
    {
        $context = $this->manager->contextFor($user);

        $locationChanged = $context->country_code !== $location->countryCode
            || $context->region !== $location->region
            || $context->city !== $location->city;

        $previousTimezone = $context->timezone;

        $context->ip_address = IpPrivacyMode::configured()->apply($location->ip);
        $context->country_code = $location->countryCode;
        $context->region = $location->region;
        $context->city = $location->city;

        $newTimezone = $location->timezone;
        $timezoneChanged = $newTimezone !== null
            && $newTimezone !== $previousTimezone
            && $context->timezone_source !== ContextSource::User;

        if ($timezoneChanged) {
            $context->timezone = $newTimezone;
            $context->timezone_source = ContextSource::Ip;
        }

        $context->saveQuietly();

        if ($locationChanged) {
            UserLocationUpdated::dispatch($user, $location);
        }

        if ($timezoneChanged) {
            UserTimezoneChanged::dispatch($user, $previousTimezone, $newTimezone, ContextSource::Ip);
        }
    }
}
