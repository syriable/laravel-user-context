<?php

declare(strict_types=1);

namespace Syriable\UserContext\Data;

use Carbon\CarbonImmutable;
use Syriable\UserContext\Enums\DayPeriod;

/**
 * The result of comparing your local time with another user's local time
 * at a single instant.
 */
final readonly class TimeComparison
{
    public function __construct(
        public CarbonImmutable $theirTime,
        public CarbonImmutable $yourTime,
        public string $theirTimezone,
        public string $yourTimezone,
        public int $offsetMinutes,
        public DayPeriod $dayPeriod,
        public bool $sameDay,
        public bool $convenient,
    ) {}

    public function isNight(): bool
    {
        return $this->dayPeriod->isNight();
    }

    /**
     * Whether it is currently a reasonable local time to contact the
     * other user (inside the configured convenient-hours window).
     */
    public function isConvenientTime(): bool
    {
        return $this->convenient;
    }

    public function offsetHours(): float
    {
        return $this->offsetMinutes / 60;
    }

    /**
     * The offset formatted as "+12:00" / "-03:30" / "±00:00".
     */
    public function formattedOffset(): string
    {
        $sign = $this->offsetMinutes < 0 ? '-' : '+';
        $minutes = abs($this->offsetMinutes);

        return sprintf('%s%02d:%02d', $sign, intdiv($minutes, 60), $minutes % 60);
    }

    /**
     * @return array{
     *     their_time: string, your_time: string,
     *     their_timezone: string, your_timezone: string,
     *     offset: string, offset_minutes: int,
     *     day_period: string, is_night: bool, same_day: bool, convenient: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'their_time' => $this->theirTime->toIso8601String(),
            'your_time' => $this->yourTime->toIso8601String(),
            'their_timezone' => $this->theirTimezone,
            'your_timezone' => $this->yourTimezone,
            'offset' => $this->formattedOffset(),
            'offset_minutes' => $this->offsetMinutes,
            'day_period' => $this->dayPeriod->value,
            'is_night' => $this->isNight(),
            'same_day' => $this->sameDay,
            'convenient' => $this->convenient,
        ];
    }
}
