<?php

declare(strict_types=1);

namespace Syriable\UserContext\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Syriable\UserContext\Models\LoginRecord;

/**
 * Fired after a successful login has been recorded on the context row.
 * `$record` is null when login history is disabled.
 */
final class UserLoginRecorded
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Model $user,
        public readonly ?LoginRecord $record,
        public readonly ?string $ipAddress,
    ) {}
}
