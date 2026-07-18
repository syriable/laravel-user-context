<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Syriable\UserContext\Http\Middleware\TrackUserContext;
use Syriable\UserContext\Models\UserContext;

describe('the tracking middleware', function (): void {
    beforeEach(function (): void {
        config()->set('user-context.activity_throttle', 0);

        Route::middleware(['web', TrackUserContext::class])->get('/tracked', function () {
            return response('ok');
        });
    });

    it('marks the authenticated user online on a request', function (): void {
        $user = makeUser();

        $this->actingAs($user)->get('/tracked')->assertOk();

        expect($user->isOnline())->toBeTrue();
    });

    it('does nothing for a guest request', function (): void {
        $this->get('/tracked')->assertOk();

        expect(UserContext::query()->count())->toBe(0);
    });

    it('detects the locale from the Accept-Language header', function (): void {
        config()->set('user-context.locale.detect_from_header', true);
        $user = makeUser();

        $this->actingAs($user)
            ->withHeader('Accept-Language', 'fr-FR,fr;q=0.9,en;q=0.5')
            ->get('/tracked')
            ->assertOk();

        expect($user->locale())->toBe('fr_FR');
    });
});
