<?php

declare(strict_types=1);

namespace Syriable\UserContext\Data;

/**
 * Result of a geolocation lookup. The raw IP travels inside this DTO only
 * — what gets persisted is decided by the configured IP privacy mode.
 */
final readonly class LocationData
{
    public function __construct(
        public string $ip,
        public ?string $countryCode = null,
        public ?string $region = null,
        public ?string $city = null,
        public ?string $timezone = null,
    ) {}

    /**
     * @param  array{ip: string, country_code?: string|null, region?: string|null, city?: string|null, timezone?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ip: $data['ip'],
            countryCode: $data['country_code'] ?? null,
            region: $data['region'] ?? null,
            city: $data['city'] ?? null,
            timezone: $data['timezone'] ?? null,
        );
    }

    /**
     * @return array{ip: string, country_code: string|null, region: string|null, city: string|null, timezone: string|null}
     */
    public function toArray(): array
    {
        return [
            'ip' => $this->ip,
            'country_code' => $this->countryCode,
            'region' => $this->region,
            'city' => $this->city,
            'timezone' => $this->timezone,
        ];
    }
}
