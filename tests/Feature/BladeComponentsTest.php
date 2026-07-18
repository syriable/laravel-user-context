<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Syriable\UserContext\Actions\RecordUserActivity;
use Syriable\UserContext\Facades\UserContext;

/**
 * Render a Blade string through the real tag compiler, exactly as Laravel
 * does for a view using `<x-user-context::...>`. This is what regressed
 * before: components were registered with Blade::component()'s hyphen-joined
 * alias ("user-context-heartbeat") instead of a real "::" namespace, so the
 * documented tag syntax silently failed to resolve outside of direct class
 * instantiation.
 *
 * @param  array<string, mixed>  $data
 */
function renderTag(string $template, array $data = []): string
{
    return Blade::render($template, $data);
}

describe('blade components resolve through the documented :: tag syntax', function (): void {
    it('renders the presence indicator for an online user', function (): void {
        $user = makeUser();
        app(RecordUserActivity::class)($user);

        $html = renderTag('<x-user-context::user-presence :user="$user" />', ['user' => $user]);

        expect($html)->toContain('Online')
            ->and($html)->toContain('data-online="true"');
    });

    it('renders the presence indicator for an offline user', function (): void {
        $html = renderTag('<x-user-context::user-presence :user="$user" />', ['user' => makeUser()]);

        expect($html)->toContain('Offline')
            ->and($html)->toContain('data-online="false"');
    });

    it('renders another user local time with timezone and period', function (): void {
        $user = makeUser();
        UserContext::overrideTimezone($user, 'Asia/Shanghai');

        $html = renderTag('<x-user-context::local-time :user="$user" />', ['user' => $user]);

        expect($html)->toContain('Asia/Shanghai')
            ->and($html)->toContain('data-period=');
    });

    it('renders the heartbeat script pointing at the endpoint', function (): void {
        $html = renderTag('<x-user-context::heartbeat />');

        expect($html)->toContain('heartbeat')
            ->and($html)->toContain('setInterval');
    });
});
