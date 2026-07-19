<?php

declare(strict_types=1);

namespace Syriable\UserContext\Concerns;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Syriable\UserContext\Data\ContextSnapshot;
use Syriable\UserContext\Data\TimeComparison;
use Syriable\UserContext\Facades\UserContext;
use Syriable\UserContext\Models\UserContext as UserContextModel;
use Syriable\UserContext\Support\Location;
use Syriable\UserContext\Support\Presence;
use Syriable\UserContext\Support\Timezone;

/**
 * Convenience API for the authenticatable model. Every method delegates
 * to the UserContext service — the trait adds no state of its own, so a
 * model without it can still be passed to the facade directly.
 *
 * @mixin Model
 */
trait HasUserContext
{
    /**
     * @return MorphOne<UserContextModel, $this>
     */
    public function userContext(): MorphOne
    {
        /** @var class-string<UserContextModel> $model */
        $model = config('user-context.models.context', UserContextModel::class);

        return $this->morphOne($model, 'user');
    }

    public function presence(): Presence
    {
        return UserContext::presenceFor($this);
    }

    public function location(): Location
    {
        return UserContext::locationFor($this);
    }

    public function timezone(): Timezone
    {
        return UserContext::timezoneFor($this);
    }

    public function isOnline(): bool
    {
        return UserContext::isOnline($this);
    }

    /**
     * The user's current local time.
     */
    public function localTime(): CarbonImmutable
    {
        return $this->timezone()->now();
    }

    public function isNight(): bool
    {
        return $this->timezone()->isNight();
    }

    public function greeting(): string
    {
        return $this->timezone()->greeting();
    }

    public function locale(): ?string
    {
        return UserContext::contextFor($this)->locale;
    }

    /**
     * Compare this user's local time with another user's local time.
     */
    public function timeFor(Model $other): TimeComparison
    {
        return UserContext::compare($this, $other);
    }

    public function contextSnapshot(): ContextSnapshot
    {
        return UserContext::for($this);
    }
}
