<?php

declare(strict_types=1);

namespace Syriable\UserContext\View\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;
use Syriable\UserContext\Facades\UserContext;

/**
 * <x-user-context::local-time :user="$user" format="H:i" />
 *
 * Renders another user's current local time.
 */
final class LocalTime extends Component
{
    public string $time;

    public string $timezone;

    public string $period;

    public function __construct(public Model $user, public string $format = 'H:i')
    {
        $timezone = UserContext::timezoneFor($user);

        $this->time = $timezone->now()->format($format);
        $this->timezone = $timezone->effective();
        $this->period = $timezone->dayPeriod()->value;
    }

    public function render(): string
    {
        return 'user-context::local-time';
    }
}
