<?php

declare(strict_types=1);

namespace Syriable\UserContext\Data;

use Carbon\CarbonImmutable;
use Syriable\UserContext\Enums\DayPeriod;
use Syriable\UserContext\Models\UserContext;
use Syriable\UserContext\Support\AcceptLanguage;
use Syriable\UserContext\Support\Country;
use Syriable\UserContext\Support\Timezone;

/**
 * A complete, serializable snapshot of a user's context at one instant.
 */
final readonly class ContextSnapshot
{
    public function __construct(
        public bool $online,
        public ?CarbonImmutable $lastSeen,
        public ?CarbonImmutable $lastLogin,
        public ?string $ipAddress,
        public ?string $countryCode,
        public ?string $countryName,
        public ?string $region,
        public ?string $city,
        public string $timezone,
        public CarbonImmutable $localTime,
        public DayPeriod $dayPeriod,
        public ?string $locale,
        public ?string $language,
    ) {}

    public static function fromContext(UserContext $context): self
    {
        $timezone = new Timezone($context->timezone);

        return new self(
            online: $context->isCurrentlyOnline(),
            lastSeen: $context->last_seen_at,
            lastLogin: $context->last_login_at,
            ipAddress: $context->ip_address,
            countryCode: $context->country_code,
            countryName: Country::name($context->country_code),
            region: $context->region,
            city: $context->city,
            timezone: $timezone->effective(),
            localTime: $timezone->now(),
            dayPeriod: $timezone->dayPeriod(),
            locale: $context->locale,
            language: $context->locale !== null ? AcceptLanguage::language($context->locale) : null,
        );
    }

    /**
     * @return array{
     *     online: bool, last_seen: string|null, last_login: string|null,
     *     ip_address: string|null, country_code: string|null, country: string|null,
     *     region: string|null, city: string|null, timezone: string,
     *     local_time: string, day_period: string, locale: string|null, language: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'online' => $this->online,
            'last_seen' => $this->lastSeen?->toIso8601String(),
            'last_login' => $this->lastLogin?->toIso8601String(),
            'ip_address' => $this->ipAddress,
            'country_code' => $this->countryCode,
            'country' => $this->countryName,
            'region' => $this->region,
            'city' => $this->city,
            'timezone' => $this->timezone,
            'local_time' => $this->localTime->format('H:i'),
            'day_period' => $this->dayPeriod->value,
            'locale' => $this->locale,
            'language' => $this->language,
        ];
    }
}
