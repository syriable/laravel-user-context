<?php

declare(strict_types=1);

namespace Syriable\UserContext\Presence;

use Carbon\CarbonImmutable;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Syriable\UserContext\Contracts\PresenceSource;

/**
 * Presence sourced from Laravel's own `sessions` table. Laravel's
 * DatabaseSessionHandler already writes `last_activity`, `ip_address` and
 * `user_agent` for the authenticated user on every request, so there is
 * nothing for the package to duplicate: last-seen, online status and the
 * network signals are read straight from the framework's table.
 *
 * Online status still uses the package's own `online_timeout`, keeping
 * presence semantics independent of `session.lifetime`.
 *
 * Note: `sessions.user_id` is a single scalar (the guard identifier) with
 * no morph type, so this source keys purely on the user's primary key and
 * suits applications with a single authenticatable type. Multi-guard /
 * polymorphic setups should use the `table` source.
 */
final readonly class SessionPresenceSource implements PresenceSource
{
    public function __construct(private DatabaseManager $db) {}

    public function lastSeenAt(Model $user): ?CarbonImmutable
    {
        $lastActivity = $this->query()
            ->where('user_id', $user->getKey())
            ->max('last_activity');

        return $lastActivity !== null
            ? CarbonImmutable::createFromTimestamp((int) $lastActivity)
            : null;
    }

    public function isOnline(Model $user): bool
    {
        $cutoff = CarbonImmutable::now()->subSeconds($this->timeout())->getTimestamp();

        return $this->query()
            ->where('user_id', $user->getKey())
            ->where('last_activity', '>', $cutoff)
            ->exists();
    }

    public function ipAddress(Model $user): ?string
    {
        return $this->latestValue($user, 'ip_address');
    }

    public function userAgent(Model $user): ?string
    {
        return $this->latestValue($user, 'user_agent');
    }

    private function latestValue(Model $user, string $column): ?string
    {
        $row = $this->query()
            ->where('user_id', $user->getKey())
            ->orderByDesc('last_activity')
            ->first([$column]);

        if ($row === null) {
            return null;
        }

        $value = ((array) $row)[$column] ?? null;

        return is_string($value) ? $value : null;
    }

    private function query(): Builder
    {
        $connection = config('session.connection');
        $table = config('session.table', 'sessions');

        return $this->db
            ->connection(is_string($connection) ? $connection : null)
            ->table(is_string($table) ? $table : 'sessions');
    }

    private function timeout(): int
    {
        return (int) config('user-context.online_timeout', 300);
    }
}
