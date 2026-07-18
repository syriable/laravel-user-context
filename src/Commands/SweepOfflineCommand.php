<?php

declare(strict_types=1);

namespace Syriable\UserContext\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Syriable\UserContext\Events\UserOffline;
use Syriable\UserContext\Models\UserContext;

/**
 * Flags users whose activity timed out as offline and fires a UserOffline
 * event for each transition. Only needed by applications that listen for
 * UserOffline — presence reads stay correct without it, since they are
 * computed against the timeout on every check.
 *
 * Schedule it: $schedule->command('user-context:sweep-offline')->everyMinute();
 */
final class SweepOfflineCommand extends Command
{
    protected $signature = 'user-context:sweep-offline';

    protected $description = 'Mark users whose activity timed out as offline and fire UserOffline events';

    public function handle(): int
    {
        $model = $this->contextModel();
        $swept = 0;

        $model::query()
            ->where('is_online', true)
            ->where(function (Builder $query): void {
                $query->whereNull('last_seen_at')
                    ->orWhere('last_seen_at', '<=', UserContext::onlineCutoff());
            })
            ->with('user')
            ->chunkById(500, function (Collection $contexts) use ($model, &$swept): void {
                $model::query()
                    ->whereKey($contexts->modelKeys())
                    ->update(['is_online' => false]);

                foreach ($contexts as $context) {
                    $swept++;

                    if ($context->user !== null) {
                        UserOffline::dispatch($context->user, $context->last_seen_at);
                    }
                }
            });

        $this->info("Swept {$swept} user(s) offline.");

        return self::SUCCESS;
    }

    /**
     * @return class-string<UserContext>
     */
    private function contextModel(): string
    {
        $model = config('user-context.models.context', UserContext::class);

        if (! is_string($model) || ! is_a($model, UserContext::class, true)) {
            return UserContext::class;
        }

        return $model;
    }
}
