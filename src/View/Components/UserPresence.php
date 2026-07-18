<?php

declare(strict_types=1);

namespace Syriable\UserContext\View\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;
use Syriable\UserContext\Facades\UserContext;

/**
 * <x-user-context::user-presence :user="$user" />
 *
 * Renders an online/offline indicator for a user.
 */
final class UserPresence extends Component
{
    public bool $online;

    public function __construct(public Model $user)
    {
        $this->online = UserContext::isOnline($user);
    }

    public function render(): string
    {
        return 'user-context::presence';
    }
}
