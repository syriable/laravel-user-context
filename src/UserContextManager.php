<?php

declare(strict_types=1);

namespace Syriable\UserContext;

use Closure;
use DateTimeZone;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Syriable\UserContext\Contracts\GeolocationProvider;
use Syriable\UserContext\Data\ContextSnapshot;
use Syriable\UserContext\Data\TimeComparison;
use Syriable\UserContext\Enums\ContextSource;
use Syriable\UserContext\Events\UserTimezoneChanged;
use Syriable\UserContext\Exceptions\InvalidLocale;
use Syriable\UserContext\Exceptions\InvalidTimezone;
use Syriable\UserContext\Geolocation\GeolocationManager;
use Syriable\UserContext\Models\UserContext;
use Syriable\UserContext\Support\AcceptLanguage;
use Syriable\UserContext\Support\Timezone;

/**
 * The package's coordinating service, exposed through the UserContext
 * facade. Works with any Eloquent user model — no trait required —
 * although the HasUserContext trait is the nicer way in.
 */
final class UserContextManager
{
    public function __construct(private readonly Container $container) {}

    /**
     * The context row for a user — an unsaved instance when the user has
     * never been tracked, so reads are always null-safe.
     */
    public function contextFor(Model $user): UserContext
    {
        $model = $this->contextModel();

        return $model::query()->firstOrNew([
            'user_type' => $user->getMorphClass(),
            'user_id' => $user->getKey(),
        ]);
    }

    /**
     * A complete serializable snapshot of the user's context.
     */
    public function for(Model $user): ContextSnapshot
    {
        return ContextSnapshot::fromContext($this->contextFor($user));
    }

    public function isOnline(Model $user): bool
    {
        return $this->contextFor($user)->isCurrentlyOnline();
    }

    public function timezoneFor(Model $user): Timezone
    {
        return new Timezone($this->contextFor($user)->timezone);
    }

    /**
     * Compare your local time with another user's local time right now.
     */
    public function compare(Model $you, Model $them): TimeComparison
    {
        $your = $this->timezoneFor($you);
        $their = $this->timezoneFor($them);

        $yourNow = $your->now();
        $theirNow = $their->now();

        return new TimeComparison(
            theirTime: $theirNow,
            yourTime: $yourNow,
            theirTimezone: $their->effective(),
            yourTimezone: $your->effective(),
            offsetMinutes: $theirNow->utcOffset() - $yourNow->utcOffset(),
            dayPeriod: $their->dayPeriod(),
            sameDay: $theirNow->toDateString() === $yourNow->toDateString(),
            convenient: $their->isConvenientTime(),
        );
    }

    /**
     * Query builder over context rows of currently online users.
     *
     * @return Builder<UserContext>
     */
    public function online(): Builder
    {
        return $this->contextModel()::query()->online();
    }

    /**
     * Explicitly set a user's timezone. A user-set timezone always wins
     * over IP detection.
     *
     * @throws InvalidTimezone
     */
    public function overrideTimezone(Model $user, string $timezone): void
    {
        if (! in_array($timezone, DateTimeZone::listIdentifiers(), true)) {
            throw InvalidTimezone::make($timezone);
        }

        $context = $this->contextFor($user);
        $previous = $context->timezone;

        $context->timezone = $timezone;
        $context->timezone_source = ContextSource::User;
        $context->saveQuietly();

        if ($previous !== $timezone) {
            UserTimezoneChanged::dispatch($user, $previous, $timezone, ContextSource::User);
        }
    }

    /**
     * Explicitly set a user's locale. A user-set locale always wins over
     * Accept-Language detection.
     *
     * @throws InvalidLocale
     */
    public function overrideLocale(Model $user, string $locale): void
    {
        $normalized = AcceptLanguage::parse($locale);

        if ($normalized === null) {
            throw InvalidLocale::make($locale);
        }

        $context = $this->contextFor($user);
        $context->locale = $normalized;
        $context->locale_source = ContextSource::User;
        $context->saveQuietly();
    }

    /**
     * Register a custom geolocation driver, selectable via the
     * `user-context.geolocation.driver` config key.
     *
     * @param  Closure(): GeolocationProvider  $callback
     */
    public function extendGeolocation(string $driver, Closure $callback): void
    {
        $this->container->make(GeolocationManager::class)->extend($driver, $callback);
    }

    /**
     * @return class-string<UserContext>
     */
    private function contextModel(): string
    {
        $model = config('user-context.models.context', UserContext::class);

        if (! is_string($model) || ! is_a($model, UserContext::class, true)) {
            return UserContext::class;
        }

        return $model;
    }
}
