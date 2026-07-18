<?php

declare(strict_types=1);

namespace Syriable\UserContext\Events;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired once when a user transitions from offline to online.
 */
final class UserOnline
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Model $user,
        public readonly CarbonImmutable $seenAt,
    ) {}
}
