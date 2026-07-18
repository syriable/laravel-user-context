<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Syriable\UserContext\Enums\DayPeriod;
use Syriable\UserContext\Facades\UserContext;

describe('user-to-user time comparison', function (): void {
    beforeEach(function (): void {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-18 14:30:00', 'UTC'));
    });

    afterEach(function (): void {
        CarbonImmutable::setTestNow();
    });

    it('reports the offset between two users in different timezones', function (): void {
        $newYork = makeUser();
        $shanghai = makeUser();

        UserContext::overrideTimezone($newYork, 'America/New_York');
        UserContext::overrideTimezone($shanghai, 'Asia/Shanghai');

        $comparison = $newYork->timeFor($shanghai);

        // July: New York is UTC-4, Shanghai is UTC+8 → +12h.
        expect($comparison->offsetMinutes)->toBe(720)
            ->and($comparison->offsetHours())->toBe(12.0)
            ->and($comparison->formattedOffset())->toBe('+12:00')
            ->and($comparison->theirTimezone)->toBe('Asia/Shanghai')
            ->and($comparison->yourTimezone)->toBe('America/New_York');
    });

    it('classifies the recipient day period and night status', function (): void {
        $newYork = makeUser();
        $shanghai = makeUser();

        UserContext::overrideTimezone($newYork, 'America/New_York');
        UserContext::overrideTimezone($shanghai, 'Asia/Shanghai');

        // 14:30 UTC → 22:30 in Shanghai → night.
        $comparison = $newYork->timeFor($shanghai);

        expect($comparison->dayPeriod)->toBe(DayPeriod::Night)
            ->and($comparison->isNight())->toBeTrue()
            ->and($comparison->isConvenientTime())->toBeFalse();
    });

    it('flags a convenient contact time inside the configured window', function (): void {
        $london = makeUser();
        $berlin = makeUser();

        UserContext::overrideTimezone($london, 'Europe/London');
        UserContext::overrideTimezone($berlin, 'Europe/Berlin');

        // 14:30 UTC → 16:30 in Berlin → convenient.
        expect($london->timeFor($berlin)->isConvenientTime())->toBeTrue();
    });

    it('serializes the comparison to an array', function (): void {
        $a = makeUser();
        $b = makeUser();
        UserContext::overrideTimezone($a, 'Europe/London');
        UserContext::overrideTimezone($b, 'Asia/Shanghai');

        $array = $a->timeFor($b)->toArray();

        expect($array)->toHaveKeys([
            'their_time', 'your_time', 'their_timezone', 'your_timezone',
            'offset', 'offset_minutes', 'day_period', 'is_night', 'same_day', 'convenient',
        ]);
    });
});
