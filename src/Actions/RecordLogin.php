<?php

declare(strict_types=1);

namespace Syriable\UserContext\Actions;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Syriable\UserContext\Actions\Concerns\EnrichesContext;
use Syriable\UserContext\Enums\IpPrivacyMode;
use Syriable\UserContext\Events\UserLoginRecorded;
use Syriable\UserContext\Events\UserOnline;
use Syriable\UserContext\Models\LoginRecord;
use Syriable\UserContext\UserContextManager;

/**
 * Records a successful login: updates the context row, appends a row to
 * the login ledger (when enabled) and schedules a geolocation lookup.
 */
final readonly class RecordLogin
{
    use EnrichesContext;

    public function __construct(
        private UserContextManager $manager,
        private MaybeResolveUserLocation $maybeResolveLocation,
    ) {}

    public function __invoke(
        Model $user,
        ?string $ip = null,
        ?string $userAgent = null,
        ?string $acceptLanguage = null,
    ): void {
        $now = CarbonImmutable::now();
        $context = $this->manager->contextFor($user);
        $wasOnline = $context->exists && $context->isCurrentlyOnline();
        $storedIp = IpPrivacyMode::configured()->apply($ip);

        $context->last_login_at = $now;
        $context->last_seen_at = $now;
        $context->is_online = true;

        if ($storedIp !== null) {
            $context->ip_address = $storedIp;
        }

        $this->detectLocale($context, $acceptLanguage);
        $this->collectAgent($context, $userAgent);

        $context->saveQuietly();

        $record = $this->appendLoginRecord($user, $context->country_code, $context->city, $context->timezone, $storedIp, $userAgent, $now);

        UserLoginRecorded::dispatch($user, $record, $storedIp);

        if (! $wasOnline) {
            UserOnline::dispatch($user, $now);
        }

        ($this->maybeResolveLocation)($user, $ip);
    }

    private function appendLoginRecord(
        Model $user,
        ?string $countryCode,
        ?string $city,
        ?string $timezone,
        ?string $storedIp,
        ?string $userAgent,
        CarbonImmutable $now,
    ): ?LoginRecord {
        if (! (bool) config('user-context.login_history.enabled', true)) {
            return null;
        }

        return $this->loginRecordModel()::query()->create([
            'user_type' => $user->getMorphClass(),
            'user_id' => $user->getKey(),
            'ip_address' => $storedIp,
            'country_code' => $countryCode,
            'city' => $city,
            'timezone' => $timezone,
            'user_agent' => $this->agentForRecord($userAgent),
            'logged_in_at' => $now,
        ]);
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
