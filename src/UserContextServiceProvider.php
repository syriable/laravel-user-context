<?php

namespace Syriable\UserContext;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Syriable\UserContext\Commands\UserContextCommand;

class UserContextServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-user-context')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_user_context_table')
            ->hasCommand(UserContextCommand::class);
    }
}
