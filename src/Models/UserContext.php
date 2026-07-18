<?php

declare(strict_types=1);

namespace Syriable\UserContext\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Syriable\UserContext\Database\Factories\UserContextFactory;
use Syriable\UserContext\Enums\ContextSource;

/**
 * The per-user context snapshot: presence, location, timezone and locale.
 * Not final on purpose — consumers may extend it and swap it in via the
 * `user-context.models.context` config key.
 *
 * @property int $id
 * @property string $user_type
 * @property int $user_id
 * @property CarbonImmutable|null $last_seen_at
 * @property bool $is_online
 * @property CarbonImmutable|null $last_login_at
 * @property CarbonImmutable|null $last_logout_at
 * @property string|null $ip_address
 * @property string|null $country_code
 * @property string|null $region
 * @property string|null $city
 * @property string|null $timezone
 * @property ContextSource|null $timezone_source
 * @property string|null $locale
 * @property ContextSource|null $locale_source
 * @property array<string, mixed>|null $agent
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Model|null $user
 */
class UserContext extends Model
{
    /** @use HasFactory<UserContextFactory> */
    use HasFactory;

    protected $table = 'user_contexts';

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_seen_at' => 'immutable_datetime',
            'last_login_at' => 'immutable_datetime',
            'last_logout_at' => 'immutable_datetime',
            'is_online' => 'boolean',
            'timezone_source' => ContextSource::class,
            'locale_source' => ContextSource::class,
            'agent' => 'array',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * A user is online when their last activity is within the configured
     * timeout AND the `is_online` flag is set. The timeout guards against
     * stale flags (a vanished browser never logs out); the flag makes an
     * explicit logout take effect immediately while `last_seen_at` keeps
     * its "last activity" meaning.
     */
    public function isCurrentlyOnline(): bool
    {
        return $this->is_online
            && $this->last_seen_at !== null
            && $this->last_seen_at->greaterThan(self::onlineCutoff());
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOnline(Builder $query): Builder
    {
        return $query
            ->where('is_online', true)
            ->where('last_seen_at', '>', self::onlineCutoff());
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOffline(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where('is_online', false)
                ->orWhereNull('last_seen_at')
                ->orWhere('last_seen_at', '<=', self::onlineCutoff());
        });
    }

    public static function onlineCutoff(): CarbonImmutable
    {
        $timeout = (int) config('user-context.online_timeout', 300);

        return CarbonImmutable::now()->subSeconds($timeout);
    }

    protected static function newFactory(): UserContextFactory
    {
        return UserContextFactory::new();
    }
}
