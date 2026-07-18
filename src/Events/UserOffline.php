<?php

declare(strict_types=1);

namespace Syriable\UserContext\Events;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired once when a user transitions from online to offline — either by
 * logging out or by being swept after the online timeout elapsed.
 */
final class UserOffline
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Model $user,
        public readonly ?CarbonImmutable $lastSeenAt,
    ) {}
}
