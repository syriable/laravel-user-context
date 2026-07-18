<?php

declare(strict_types=1);

namespace Syriable\UserContext;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Syriable\UserContext\Commands\PruneLoginRecordsCommand;
use Syriable\UserContext\Commands\SweepOfflineCommand;
use Syriable\UserContext\Contracts\GeolocationProvider;
use Syriable\UserContext\Geolocation\CachingGeolocator;
use Syriable\UserContext\Geolocation\GeolocationManager;
use Syriable\UserContext\Http\Middleware\TrackUserContext;
use Syriable\UserContext\Listeners\HandleLogout;
use Syriable\UserContext\Listeners\HandleSuccessfulLogin;
use Syriable\UserContext\Support\PackageCache;

final class UserContextServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-user-context')
            ->hasConfigFile()
            ->hasViews('user-context')
            ->hasTranslations()
            ->hasMigrations([
                'create_user_contexts_table',
                'create_user_login_records_table',
            ])
            ->hasCommands([
                SweepOfflineCommand::class,
                PruneLoginRecordsCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(UserContextManager::class);

        $this->app->singleton(GeolocationManager::class, fn (Application $app): GeolocationManager => new GeolocationManager($app));

        $this->app->bind(GeolocationProvider::class, function (Application $app): GeolocationProvider {
            $provider = $app->make(GeolocationManager::class)->driver();

            $ttl = (int) config('user-context.geolocation.cache_ttl', 604800);

            if ($ttl <= 0) {
                return $provider;
            }

            /** @var Repository $cache */
            $cache = PackageCache::store();

            return new CachingGeolocator($provider, $cache, $ttl);
        });
    }

    public function packageBooted(): void
    {
        $this->registerBladeComponents();
        $this->registerRoutes();
        $this->registerAuthListeners();
    }

    private function registerBladeComponents(): void
    {
        // Blade::component()'s $prefix joins with a hyphen ("user-context-heartbeat"),
        // not "::". componentNamespace() is what actually resolves <x-user-context::heartbeat />
        // by kebab-case-matching the tag name to a class in this namespace.
        Blade::componentNamespace('Syriable\\UserContext\\View\\Components', 'user-context');
    }

    private function registerRoutes(): void
    {
        if (! (bool) config('user-context.routes.enabled', true)) {
            return;
        }

        $middleware = config('user-context.routes.middleware', ['web', 'auth']);

        Route::group([
            'prefix' => config('user-context.routes.prefix', 'user-context'),
            'middleware' => is_array($middleware) ? $middleware : ['web', 'auth'],
            'as' => 'user-context.',
        ], function (): void {
            $this->loadRoutesFrom(__DIR__.'/../routes/user-context.php');
        });
    }

    private function registerAuthListeners(): void
    {
        if ((bool) config('user-context.login_history.enabled', true) || (bool) config('user-context.geolocation.enabled', true)) {
            Event::listen(Login::class, HandleSuccessfulLogin::class);
        }

        Event::listen(Logout::class, HandleLogout::class);
    }

    /**
     * @return array<int, class-string>
     */
    public function provides(): array
    {
        return [
            UserContextManager::class,
            GeolocationManager::class,
            GeolocationProvider::class,
            TrackUserContext::class,
        ];
    }
}
