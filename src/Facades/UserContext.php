<?php

declare(strict_types=1);

namespace Syriable\UserContext\Facades;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use Syriable\UserContext\Data\ContextSnapshot;
use Syriable\UserContext\Data\TimeComparison;
use Syriable\UserContext\Support\Location;
use Syriable\UserContext\Support\Presence;
use Syriable\UserContext\Support\Timezone;
use Syriable\UserContext\UserContextManager;

/**
 * @method static \Syriable\UserContext\Models\UserContext contextFor(Model $user)
 * @method static ContextSnapshot for(Model $user)
 * @method static Presence presenceFor(Model $user)
 * @method static Location locationFor(Model $user)
 * @method static bool isOnline(Model $user)
 * @method static Timezone timezoneFor(Model $user)
 * @method static TimeComparison compare(Model $you, Model $them)
 * @method static Builder<\Syriable\UserContext\Models\UserContext> online()
 * @method static void overrideTimezone(Model $user, string $timezone)
 * @method static void overrideLocale(Model $user, string $locale)
 * @method static void extendGeolocation(string $driver, Closure $callback)
 *
 * @see UserContextManager
 */
final class UserContext extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return UserContextManager::class;
    }
}
