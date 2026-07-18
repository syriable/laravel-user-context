<?php

declare(strict_types=1);

use Illuminate\Support\Facades\View;
use Illuminate\View\Component;
use Syriable\UserContext\Actions\RecordUserActivity;
use Syriable\UserContext\Facades\UserContext;
use Syriable\UserContext\View\Components\Heartbeat;
use Syriable\UserContext\View\Components\LocalTime;
use Syriable\UserContext\View\Components\UserPresence;

/**
 * Render a component class through its own view + data, exactly as the
 * Blade component pipeline does when the `<x-user-context::...>` tag is used.
 */
function renderComponent(Component $component): string
{
    return View::make($component->render(), $component->data())->render();
}

describe('blade components', function (): void {
    it('renders the presence indicator for an online user', function (): void {
        $user = makeUser();
        app(RecordUserActivity::class)($user);

        $html = renderComponent(new UserPresence($user));

        expect($html)->toContain('Online')
            ->and($html)->toContain('data-online="true"');
    });

    it('renders the presence indicator for an offline user', function (): void {
        $html = renderComponent(new UserPresence(makeUser()));

        expect($html)->toContain('Offline')
            ->and($html)->toContain('data-online="false"');
    });

    it('renders another user local time with timezone and period', function (): void {
        $user = makeUser();
        UserContext::overrideTimezone($user, 'Asia/Shanghai');

        $html = renderComponent(new LocalTime($user));

        expect($html)->toContain('Asia/Shanghai')
            ->and($html)->toContain('data-period=');
    });

    it('renders the heartbeat script pointing at the endpoint', function (): void {
        $html = renderComponent(new Heartbeat);

        expect($html)->toContain('heartbeat')
            ->and($html)->toContain('setInterval');
    });
});
