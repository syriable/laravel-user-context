<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Syriable\UserContext\Enums\DayPeriod;
use Syriable\UserContext\Support\Timezone;

describe('Timezone', function (): void {
    it('falls back to the application timezone when none is known', function (): void {
        config()->set('app.timezone', 'Europe/Stockholm');

        $timezone = new Timezone(null);

        expect($timezone->isKnown())->toBeFalse()
            ->and($timezone->effective())->toBe('Europe/Stockholm');
    });

    it('uses the stored identifier when known', function (): void {
        $timezone = new Timezone('Asia/Shanghai');

        expect($timezone->isKnown())->toBeTrue()
            ->and($timezone->name())->toBe('Asia/Shanghai')
            ->and($timezone->effective())->toBe('Asia/Shanghai');
    });

    it('converts a moment into the local timezone', function (): void {
        $timezone = new Timezone('Asia/Shanghai');
        $utcNoon = CarbonImmutable::parse('2026-07-18 12:00:00', 'UTC');

        expect($timezone->toLocal($utcNoon)->format('H:i'))->toBe('20:00');
    });

    it('is daylight-saving aware for the current offset', function (): void {
        // New York is UTC-4 in July (EDT), not the winter UTC-5.
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-18 12:00:00', 'UTC'));

        expect((new Timezone('America/New_York'))->offsetMinutes())->toBe(-240);

        CarbonImmutable::setTestNow();
    });

    it('derives the day period and greeting from local time', function (): void {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-18 01:00:00', 'UTC'));

        // 09:00 local in Shanghai → morning.
        $timezone = new Timezone('Asia/Shanghai');

        expect($timezone->dayPeriod())->toBe(DayPeriod::Morning)
            ->and($timezone->isNight())->toBeFalse()
            ->and($timezone->greeting())->toBe('Good morning');

        CarbonImmutable::setTestNow();
    });

    it('reports night during configured night hours', function (): void {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-18 14:00:00', 'UTC'));

        // 22:00 local in Shanghai → night.
        expect((new Timezone('Asia/Shanghai'))->isNight())->toBeTrue();

        CarbonImmutable::setTestNow();
    });
});

describe('DayPeriod::fromHour', function (): void {
    it('maps hours to periods using the configured boundaries', function (int $hour, DayPeriod $period): void {
        expect(DayPeriod::fromHour($hour))->toBe($period);
    })->with([
        [6, DayPeriod::Morning],
        [11, DayPeriod::Morning],
        [12, DayPeriod::Afternoon],
        [16, DayPeriod::Afternoon],
        [17, DayPeriod::Evening],
        [20, DayPeriod::Evening],
        [21, DayPeriod::Night],
        [3, DayPeriod::Night],
    ]);
});
