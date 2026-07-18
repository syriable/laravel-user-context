<?php

declare(strict_types=1);

namespace Syriable\UserContext\View\Components;

use Illuminate\View\Component;

/**
 * <x-user-context::heartbeat />
 *
 * Emits a small script that pings the heartbeat endpoint on an interval
 * to keep the current user online while the tab is open.
 */
final class Heartbeat extends Component
{
    public string $endpoint;

    public int $interval;

    public function __construct(?int $interval = null)
    {
        $this->endpoint = url(
            trim((string) config('user-context.routes.prefix', 'user-context'), '/').'/heartbeat'
        );
        $this->interval = $interval ?? (int) config('user-context.heartbeat.interval', 60);
    }

    public function render(): string
    {
        return 'user-context::heartbeat';
    }
}
