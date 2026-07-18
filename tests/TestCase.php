<?php

declare(strict_types=1);

namespace Syriable\UserContext\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Syriable\UserContext\UserContextServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => 'Syriable\\UserContext\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        $this->setUpDatabase();
    }

    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            UserContextServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        config()->set('cache.default', 'array');
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        // Never make real geolocation HTTP calls during the test suite;
        // geolocation behavior is exercised with explicit fake providers.
        config()->set('user-context.geolocation.driver', 'null');
    }

    private function setUpDatabase(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
        });

        foreach (['create_user_contexts_table', 'create_user_login_records_table'] as $migration) {
            (require __DIR__."/../database/migrations/{$migration}.php.stub")->up();
        }
    }
}
