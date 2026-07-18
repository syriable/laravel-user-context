<?php

declare(strict_types=1);

namespace Syriable\UserContext\Support;

use Carbon\CarbonImmutable;
use Syriable\UserContext\Models\UserContext;

/**
 * Read-side proxy over a user's presence state. Null-safe: a user who
 * has never been tracked simply reads as offline.
 */
final readonly class Presence
{
    public function __construct(private ?UserContext $context) {}

    public function isOnline(): bool
    {
        return $this->context?->isCurrentlyOnline() ?? false;
    }

    public function isOffline(): bool
    {
        return ! $this->isOnline();
    }

    public function lastSeen(): ?CarbonImmutable
    {
        return $this->context?->last_seen_at;
    }

    public function lastLogin(): ?CarbonImmutable
    {
        return $this->context?->last_login_at;
    }

    public function lastLogout(): ?CarbonImmutable
    {
        return $this->context?->last_logout_at;
    }

    /**
     * "online" or "offline" — convenient for templates and APIs.
     */
    public function status(): string
    {
        return $this->isOnline() ? 'online' : 'offline';
    }
}
