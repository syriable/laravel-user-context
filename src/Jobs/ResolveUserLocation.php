<?php

declare(strict_types=1);

namespace Syriable\UserContext\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Syriable\UserContext\Actions\UpdateUserLocation;
use Syriable\UserContext\Contracts\GeolocationProvider;

/**
 * Resolves an IP address to a location and applies it to the user's
 * context. Queue-friendly, never queue-dependent: with the default
 * configuration (`user-context.queue.enabled` = false) the job runs
 * synchronously in-process.
 */
final class ResolveUserLocation implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Model $user,
        public readonly string $ip,
    ) {}

    public function handle(GeolocationProvider $geolocator, UpdateUserLocation $updateLocation): void
    {
        $location = $geolocator->locate($this->ip);

        if ($location === null) {
            return;
        }

        $updateLocation($this->user, $location);
    }

    /**
     * Dispatch honoring the package queue configuration: synchronously by
     * default, onto the configured connection/queue when enabled.
     */
    public static function dispatchUsingConfig(Model $user, string $ip): void
    {
        $job = new self($user, $ip);

        if (! (bool) config('user-context.queue.enabled', false)) {
            dispatch_sync($job);

            return;
        }

        $pending = dispatch($job);

        $connection = config('user-context.queue.connection');
        $queue = config('user-context.queue.queue');

        if (is_string($connection) && $connection !== '') {
            $pending->onConnection($connection);
        }

        if (is_string($queue) && $queue !== '') {
            $pending->onQueue($queue);
        }
    }
}
