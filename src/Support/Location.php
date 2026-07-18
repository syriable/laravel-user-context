<?php

declare(strict_types=1);

namespace Syriable\UserContext\Support;

use Syriable\UserContext\Models\UserContext;

/**
 * Read-side proxy over a user's last known location. Null-safe.
 */
final readonly class Location
{
    public function __construct(private ?UserContext $context) {}

    public function countryCode(): ?string
    {
        return $this->context?->country_code;
    }

    /**
     * Human-readable country name, resolved from the stored ISO code via
     * the intl extension in the given (or current) display locale.
     */
    public function countryName(?string $displayLocale = null): ?string
    {
        return Country::name($this->countryCode(), $displayLocale);
    }

    public function region(): ?string
    {
        return $this->context?->region;
    }

    public function city(): ?string
    {
        return $this->context?->city;
    }

    /**
     * The stored IP address — raw, anonymized or hashed depending on the
     * configured `user-context.ip.privacy` mode.
     */
    public function ipAddress(): ?string
    {
        return $this->context?->ip_address;
    }
}
