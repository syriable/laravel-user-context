<?php

declare(strict_types=1);

namespace Syriable\UserContext\Support;

use Illuminate\Database\Eloquent\Model;
use Syriable\UserContext\Contracts\PresenceSource;
use Syriable\UserContext\Models\UserContext;

/**
 * Read-side proxy over a user's last known location. Null-safe.
 *
 * Geolocation (country/region/city) always comes from the context row.
 * The IP address and User-Agent flow through the active PresenceSource
 * when one is supplied, so they can be served from Laravel's `sessions`
 * table.
 */
final readonly class Location
{
    public function __construct(
        private ?UserContext $context,
        private ?PresenceSource $source = null,
        private ?Model $user = null,
    ) {}

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
     * The stored IP address. In `table` presence mode this is the value
     * persisted through the configured `user-context.ip.privacy` mode
     * (raw, anonymized or hashed); in `sessions` mode it is the raw
     * address Laravel stores on the session row.
     */
    public function ipAddress(): ?string
    {
        if ($this->source !== null && $this->user !== null) {
            return $this->source->ipAddress($this->user);
        }

        return $this->context?->ip_address;
    }

    /**
     * The most recently recorded User-Agent string, or null when unknown
     * or not collected.
     */
    public function userAgent(): ?string
    {
        if ($this->source !== null && $this->user !== null) {
            return $this->source->userAgent($this->user);
        }

        $agent = $this->context?->agent;

        $value = is_array($agent) ? ($agent['user_agent'] ?? null) : null;

        return is_string($value) ? $value : null;
    }
}
