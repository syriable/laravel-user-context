<?php

declare(strict_types=1);

namespace Syriable\UserContext\Support;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Syriable\UserContext\Contracts\PresenceSource;
use Syriable\UserContext\Models\UserContext;

/**
 * Read-side proxy over a user's presence state. Null-safe: a user who
 * has never been tracked simply reads as offline.
 *
 * Online status and last-seen flow through the active PresenceSource when
 * one is supplied (so they can come from Laravel's `sessions` table);
 * login/logout timestamps always come from the context row, since the
 * sessions table does not record them.
 */
final readonly class Presence
{
    public function __construct(
        private ?UserContext $context,
        private ?PresenceSource $source = null,
        private ?Model $user = null,
    ) {}

    public function isOnline(): bool
    {
        if ($this->source !== null && $this->user !== null) {
            return $this->source->isOnline($this->user);
        }

        return $this->context?->isCurrentlyOnline() ?? false;
    }

    public function isOffline(): bool
    {
        return ! $this->isOnline();
    }

    public function lastSeen(): ?CarbonImmutable
    {
        if ($this->source !== null && $this->user !== null) {
            return $this->source->lastSeenAt($this->user);
        }

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
