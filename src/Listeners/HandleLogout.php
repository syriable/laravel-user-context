<?php

declare(strict_types=1);

namespace Syriable\UserContext\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Syriable\UserContext\Actions\RecordLogout;

/**
 * @internal
 */
final readonly class HandleLogout
{
    public function __construct(private RecordLogout $recordLogout) {}

    public function handle(Logout $event): void
    {
        if (! $event->user instanceof Model) {
            return;
        }

        ($this->recordLogout)($event->user);
    }
}
