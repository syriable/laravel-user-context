<?php

declare(strict_types=1);
use Syriable\UserContext\Facades\UserContext;

describe('the heartbeat endpoint', function (): void {
    it('marks the authenticated user online', function (): void {
        config()->set('user-context.activity_throttle', 0);
        $user = makeUser();

        $this->actingAs($user)
            ->postJson('/user-context/heartbeat')
            ->assertOk()
            ->assertJson(['online' => true]);

        expect($user->isOnline())->toBeTrue();
    });

    it('rejects a guest', function (): void {
        $this->postJson('/user-context/heartbeat')->assertUnauthorized();
    });
});

describe('the /me endpoint', function (): void {
    it('returns the authenticated user context snapshot', function (): void {
        $user = makeUser();
        UserContext::overrideTimezone($user, 'Europe/Stockholm');

        $this->actingAs($user)
            ->getJson('/user-context/me')
            ->assertOk()
            ->assertJsonStructure([
                'online', 'last_seen', 'timezone', 'local_time', 'country', 'locale',
            ])
            ->assertJson(['timezone' => 'Europe/Stockholm']);
    });
});
