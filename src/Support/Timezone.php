<?php

declare(strict_types=1);

namespace Syriable\UserContext\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Stringable;
use Syriable\UserContext\Enums\DayPeriod;

/**
 * Read-side proxy around a user's (possibly unknown) IANA timezone.
 * Falls back to the application timezone when none is known, so every
 * method is always safe to call.
 */
final readonly class Timezone implements Stringable
{
    public function __construct(private ?string $timezone) {}

    /**
     * The stored IANA identifier, or null when nothing has been detected.
     */
    public function name(): ?string
    {
        return $this->timezone;
    }

    /**
     * The timezone actually used for conversions — the stored identifier
     * or the application timezone as fallback.
     */
    public function effective(): string
    {
        if ($this->timezone !== null) {
            return $this->timezone;
        }

        $fallback = config('app.timezone', 'UTC');

        return is_string($fallback) ? $fallback : 'UTC';
    }

    public function isKnown(): bool
    {
        return $this->timezone !== null;
    }

    /**
     * The current local time in this timezone.
     */
    public function now(): CarbonImmutable
    {
        return CarbonImmutable::now($this->effective());
    }

    /**
     * Convert any moment to this timezone.
     */
    public function toLocal(DateTimeInterface $moment): CarbonImmutable
    {
        return CarbonImmutable::instance($moment)->setTimezone($this->effective());
    }

    public function dayPeriod(): DayPeriod
    {
        return DayPeriod::fromHour($this->now()->hour);
    }

    public function isNight(): bool
    {
        return $this->dayPeriod()->isNight();
    }

    /**
     * A localized greeting ("Good morning", …) for the current local time.
     */
    public function greeting(): string
    {
        $period = $this->dayPeriod();
        $greeting = __('user-context::user-context.greeting.'.$period->value);

        return is_string($greeting) ? $greeting : $period->value;
    }

    /**
     * The current UTC offset in minutes (DST-aware).
     */
    public function offsetMinutes(): int
    {
        return $this->now()->utcOffset();
    }

    /**
     * Whether the current local time falls inside the configured
     * `user-context.time.convenient_hours` contact window.
     */
    public function isConvenientTime(): bool
    {
        $hour = $this->now()->hour;
        $from = (int) config('user-context.time.convenient_hours.from', 8);
        $until = (int) config('user-context.time.convenient_hours.until', 21);

        return $hour >= $from && $hour < $until;
    }

    public function __toString(): string
    {
        return $this->effective();
    }
}
