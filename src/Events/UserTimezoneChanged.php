<?php

declare(strict_types=1);

namespace Syriable\UserContext\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Syriable\UserContext\Enums\ContextSource;

/**
 * Fired when the user's timezone changes — via IP detection or an
 * explicit user override.
 */
final class UserTimezoneChanged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Model $user,
        public readonly ?string $previous,
        public readonly string $current,
        public readonly ContextSource $source,
    ) {}
}
