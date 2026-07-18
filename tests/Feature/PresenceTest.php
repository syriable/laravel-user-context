<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;
use Syriable\UserContext\Actions\RecordUserActivity;
use Syriable\UserContext\Events\UserOnline;
use Syriable\UserContext\Facades\UserContext;
use Syriable\UserContext\Models\UserContext as UserContextModel;

describe('presence detection', function (): void {
    it('reports a never-seen user as offline', function (): void {
        $user = makeUser();

        expect($user->isOnline())->toBeFalse()
            ->and($user->presence()->status())->toBe('offline')
            ->and($user->presence()->lastSeen())->toBeNull();
    });

    it('marks a user online after recording activity', function (): void {
        $user = makeUser();

        app(RecordUserActivity::class)($user, ip: '8.8.8.8');

        expect($user->isOnline())->toBeTrue()
            ->and($user->presence()->status())->toBe('online');
    });

    it('treats a user whose last activity is beyond the timeout as offline', function (): void {
        config()->set('user-context.online_timeout', 300);
        $user = makeUser();

        UserContextModel::query()->create([
            'user_type' => $user->getMorphClass(),
            'user_id' => $user->getKey(),
            'is_online' => true,
            'last_seen_at' => CarbonImmutable::now()->subMinutes(10),
        ]);

        expect($user->isOnline())->toBeFalse();
    });

    it('fires UserOnline once on the offline to online transition', function (): void {
        Event::fake([UserOnline::class]);
        $user = makeUser();
        config()->set('user-context.activity_throttle', 0);

        app(RecordUserActivity::class)($user);
        app(RecordUserActivity::class)($user);

        Event::assertDispatchedTimes(UserOnline::class, 1);
    });

    it('scopes queries to online users only', function (): void {
        $online = makeUser();
        $offline = makeUser();

        app(RecordUserActivity::class)($online);
        UserContextModel::query()->create([
            'user_type' => $offline->getMorphClass(),
            'user_id' => $offline->getKey(),
            'is_online' => true,
            'last_seen_at' => CarbonImmutable::now()->subHour(),
        ]);

        $onlineIds = UserContext::online()->pluck('user_id')->all();

        expect($onlineIds)->toContain($online->getKey())
            ->and($onlineIds)->not->toContain($offline->getKey());
    });
});

describe('activity throttling', function (): void {
    it('writes last_seen_at at most once per throttle window', function (): void {
        config()->set('user-context.activity_throttle', 60);
        $user = makeUser();

        app(RecordUserActivity::class)($user);
        $firstSeen = UserContext::contextFor($user)->last_seen_at;

        CarbonImmutable::setTestNow(CarbonImmutable::now()->addSeconds(5));
        app(RecordUserActivity::class)($user);
        $secondSeen = UserContext::contextFor($user->fresh())->last_seen_at;

        expect($secondSeen->equalTo($firstSeen))->toBeTrue();

        CarbonImmutable::setTestNow();
    });
});
