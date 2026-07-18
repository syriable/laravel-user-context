<?php

declare(strict_types=1);

namespace Syriable\UserContext\Actions;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Syriable\UserContext\Actions\Concerns\EnrichesContext;
use Syriable\UserContext\Events\UserOnline;
use Syriable\UserContext\Support\PackageCache;
use Syriable\UserContext\UserContextManager;

/**
 * Records a heartbeat of user activity: touches `last_seen_at`, fires
 * UserOnline on the offline→online transition, detects the locale and
 * schedules a geolocation lookup when the IP changed.
 *
 * Writes are throttled — at most one database write per user per
 * `activity_throttle` window, so this is safe on every request.
 */
final readonly class RecordUserActivity
{
    use EnrichesContext;

    public function __construct(
        private UserContextManager $manager,
        private MaybeResolveUserLocation $maybeResolveLocation,
    ) {}

    public function __invoke(
        Model $user,
        ?string $ip = null,
        ?string $userAgent = null,
        ?string $acceptLanguage = null,
    ): void {
        if (! $this->passesThrottle($user)) {
            return;
        }

        $context = $this->manager->contextFor($user);
        $wasOnline = $context->exists && $context->isCurrentlyOnline();
        $now = CarbonImmutable::now();

        $context->last_seen_at = $now;
        $context->is_online = true;

        $this->detectLocale($context, $acceptLanguage);
        $this->collectAgent($context, $userAgent);

        $context->saveQuietly();

        if (! $wasOnline) {
            UserOnline::dispatch($user, $now);
        }

        ($this->maybeResolveLocation)($user, $ip);
    }

    private function passesThrottle(Model $user): bool
    {
        $seconds = (int) config('user-context.activity_throttle', 60);

        if ($seconds <= 0) {
            return true;
        }

        $key = sprintf('user-context:activity:%s:%s', $user->getMorphClass(), $user->getKey());

        return PackageCache::store()->add($key, true, $seconds);
    }
}
