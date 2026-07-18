<?php

declare(strict_types=1);

namespace Syriable\UserContext\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Syriable\UserContext\Data\LocationData;

/**
 * Fired when a geolocation lookup changed the user's stored location.
 */
final class UserLocationUpdated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Model $user,
        public readonly LocationData $location,
    ) {}
}
