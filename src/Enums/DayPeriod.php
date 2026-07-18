<?php

declare(strict_types=1);

namespace Syriable\UserContext\Enums;

enum DayPeriod: string
{
    case Morning = 'morning';
    case Afternoon = 'afternoon';
    case Evening = 'evening';
    case Night = 'night';

    /**
     * Resolve the period a local hour (0-23) falls into, using the
     * configured `user-context.time.day_periods` boundaries.
     */
    public static function fromHour(int $hour): self
    {
        /** @var array{morning: int, afternoon: int, evening: int, night: int} $boundaries */
        $boundaries = config('user-context.time.day_periods', [
            'morning' => 5,
            'afternoon' => 12,
            'evening' => 17,
            'night' => 21,
        ]);

        return match (true) {
            $hour >= $boundaries['night'], $hour < $boundaries['morning'] => self::Night,
            $hour >= $boundaries['evening'] => self::Evening,
            $hour >= $boundaries['afternoon'] => self::Afternoon,
            default => self::Morning,
        };
    }

    public function isNight(): bool
    {
        return $this === self::Night;
    }
}
