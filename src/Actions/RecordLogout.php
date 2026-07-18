<?php

declare(strict_types=1);

namespace Syriable\UserContext\Actions;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Syriable\UserContext\Events\UserOffline;
use Syriable\UserContext\Models\LoginRecord;
use Syriable\UserContext\UserContextManager;

/**
 * Records a logout: stamps `last_logout_at`, closes the open login
 * ledger row and fires UserOffline. Presence itself is still computed
 * from `last_seen_at`, so a logout in one tab while another tab keeps
 * sending heartbeats simply flips the user back online.
 */
final readonly class RecordLogout
{
    public function __construct(private UserContextManager $manager) {}

    public function __invoke(Model $user): void
    {
        $now = CarbonImmutable::now();
        $context = $this->manager->contextFor($user);
        $wasOnline = $context->exists && $context->isCurrentlyOnline();
        $lastSeen = $context->last_seen_at;

        $context->last_logout_at = $now;
        $context->is_online = false;
        $context->saveQuietly();

        $this->closeOpenLoginRecord($user, $now);

        if ($wasOnline) {
            UserOffline::dispatch($user, $lastSeen);
        }
    }

    private function closeOpenLoginRecord(Model $user, CarbonImmutable $now): void
    {
        if (! (bool) config('user-context.login_history.enabled', true)) {
            return;
        }

        $model = config('user-context.models.login_record', LoginRecord::class);

        if (! is_string($model) || ! is_a($model, LoginRecord::class, true)) {
            $model = LoginRecord::class;
        }

        $model::query()
            ->where('user_type', $user->getMorphClass())
            ->where('user_id', $user->getKey())
            ->whereNull('logged_out_at')
            ->latest('logged_in_at')
            ->first()
            ?->update(['logged_out_at' => $now]);
    }
}
