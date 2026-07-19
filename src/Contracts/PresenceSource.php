<?php

declare(strict_types=1);

namespace Syriable\UserContext\Contracts;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * Supplies the presence and network signals for a user — the data that
 * Laravel's own `sessions` table already maintains. Two implementations
 * exist: one reads the package's `user_contexts` columns, the other reads
 * Laravel's `sessions` table directly. The active source is resolved from
 * the `user-context.presence.source` config key.
 */
interface PresenceSource
{
    /**
     * The instant of the user's most recent activity, or null when the
     * user has never been seen.
     */
    public function lastSeenAt(Model $user): ?CarbonImmutable;

    /**
     * Whether the user's last activity falls within the configured
     * `user-context.online_timeout` window.
     */
    public function isOnline(Model $user): bool;

    /**
     * The user's most recently recorded IP address, or null when unknown.
     */
    public function ipAddress(Model $user): ?string;

    /**
     * The user's most recently recorded User-Agent string, or null when
     * unknown or not collected.
     */
    public function userAgent(Model $user): ?string;
}
