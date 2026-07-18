<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;
use Syriable\UserContext\Events\UserOffline;
use Syriable\UserContext\Models\UserContext as UserContextModel;

describe('the offline sweep command', function (): void {
    it('flags timed-out users offline and fires UserOffline', function (): void {
        Event::fake([UserOffline::class]);
        config()->set('user-context.online_timeout', 300);
        $user = makeUser();

        UserContextModel::query()->create([
            'user_type' => $user->getMorphClass(),
            'user_id' => $user->getKey(),
            'is_online' => true,
            'last_seen_at' => CarbonImmutable::now()->subMinutes(30),
        ]);

        $this->artisan('user-context:sweep-offline')->assertSuccessful();

        expect(UserContextModel::query()->where('user_id', $user->getKey())->value('is_online'))->toBeFalse();
        Event::assertDispatched(UserOffline::class);
    });

    it('leaves still-active users untouched', function (): void {
        Event::fake([UserOffline::class]);
        $user = makeUser();

        UserContextModel::query()->create([
            'user_type' => $user->getMorphClass(),
            'user_id' => $user->getKey(),
            'is_online' => true,
            'last_seen_at' => CarbonImmutable::now(),
        ]);

        $this->artisan('user-context:sweep-offline')->assertSuccessful();

        Event::assertNotDispatched(UserOffline::class);
    });
});
