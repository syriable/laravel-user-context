<?php

declare(strict_types=1);

namespace Syriable\UserContext\Presence;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Syriable\UserContext\Contracts\PresenceSource;
use Syriable\UserContext\UserContextManager;

/**
 * Presence sourced from the package's own `user_contexts` columns — the
 * package's original behavior. Used whenever the application is not on the
 * `database` session driver (or explicitly via `presence.source = table`),
 * so presence keeps working on every session driver and for polymorphic
 * user models.
 */
final readonly class TablePresenceSource implements PresenceSource
{
    public function __construct(private UserContextManager $manager) {}

    public function lastSeenAt(Model $user): ?CarbonImmutable
    {
        return $this->manager->contextFor($user)->last_seen_at;
    }

    public function isOnline(Model $user): bool
    {
        return $this->manager->contextFor($user)->isCurrentlyOnline();
    }

    public function ipAddress(Model $user): ?string
    {
        return $this->manager->contextFor($user)->ip_address;
    }

    public function userAgent(Model $user): ?string
    {
        $agent = $this->manager->contextFor($user)->agent;

        $value = is_array($agent) ? ($agent['user_agent'] ?? null) : null;

        return is_string($value) ? $value : null;
    }
}
