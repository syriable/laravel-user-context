<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Syriable\UserContext\Events\UserLoginRecorded;
use Syriable\UserContext\Events\UserOffline;
use Syriable\UserContext\Facades\UserContext;
use Syriable\UserContext\Models\LoginRecord;

describe('login and logout recording', function (): void {
    it('records a login on the auth Login event', function (): void {
        $user = makeUser();

        event(new Login('web', $user, false));

        $context = UserContext::contextFor($user->fresh());

        expect($context->last_login_at)->not->toBeNull()
            ->and($user->isOnline())->toBeTrue()
            ->and(LoginRecord::query()->where('user_id', $user->getKey())->count())->toBe(1);
    });

    it('fires UserLoginRecorded', function (): void {
        Event::fake([UserLoginRecorded::class]);
        $user = makeUser();

        event(new Login('web', $user, false));

        Event::assertDispatched(UserLoginRecorded::class);
    });

    it('closes the open login record and fires UserOffline on logout', function (): void {
        Event::fake([UserOffline::class]);
        $user = makeUser();

        event(new Login('web', $user, false));
        event(new Logout('web', $user));

        $record = LoginRecord::query()->where('user_id', $user->getKey())->latest('logged_in_at')->first();

        expect($record->logged_out_at)->not->toBeNull()
            ->and(UserContext::contextFor($user->fresh())->last_logout_at)->not->toBeNull();

        Event::assertDispatched(UserOffline::class);
    });

    it('prunes login records older than the retention period', function (): void {
        $user = makeUser();

        LoginRecord::query()->create([
            'user_type' => $user->getMorphClass(),
            'user_id' => $user->getKey(),
            'logged_in_at' => CarbonImmutable::now()->subDays(400),
        ]);
        LoginRecord::query()->create([
            'user_type' => $user->getMorphClass(),
            'user_id' => $user->getKey(),
            'logged_in_at' => CarbonImmutable::now()->subDays(10),
        ]);

        $this->artisan('user-context:prune', ['--days' => 365])->assertSuccessful();

        expect(LoginRecord::query()->count())->toBe(1);
    });
});
