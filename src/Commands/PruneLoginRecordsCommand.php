<?php

declare(strict_types=1);

namespace Syriable\UserContext\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Syriable\UserContext\Models\LoginRecord;

/**
 * Deletes login ledger rows older than the configured retention.
 *
 * Schedule it: $schedule->command('user-context:prune')->daily();
 */
final class PruneLoginRecordsCommand extends Command
{
    protected $signature = 'user-context:prune {--days= : Override the configured retention in days}';

    protected $description = 'Prune login records older than the configured retention period';

    public function handle(): int
    {
        $days = $this->option('days');
        $days = is_numeric($days) ? (int) $days : (int) config('user-context.login_history.retention_days', 365);

        if ($days <= 0) {
            $this->warn('Retention is disabled (0 days) — nothing pruned.');

            return self::SUCCESS;
        }

        $deleted = $this->loginRecordModel()::query()
            ->where('logged_in_at', '<', CarbonImmutable::now()->subDays($days))
            ->delete();

        $this->info("Pruned {$deleted} login record(s) older than {$days} days.");

        return self::SUCCESS;
    }

    /**
     * @return class-string<LoginRecord>
     */
    private function loginRecordModel(): string
    {
        $model = config('user-context.models.login_record', LoginRecord::class);

        if (! is_string($model) || ! is_a($model, LoginRecord::class, true)) {
            return LoginRecord::class;
        }

        return $model;
    }
}
