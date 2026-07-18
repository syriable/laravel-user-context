<?php

declare(strict_types=1);

namespace Syriable\UserContext\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Syriable\UserContext\Models\UserContext;

/**
 * @extends Factory<UserContext>
 */
final class UserContextFactory extends Factory
{
    protected $model = UserContext::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_type' => 'user',
            'user_id' => $this->faker->unique()->numberBetween(1, 1_000_000),
            'last_seen_at' => null,
            'is_online' => false,
            'last_login_at' => null,
            'last_logout_at' => null,
            'ip_address' => null,
            'country_code' => null,
            'region' => null,
            'city' => null,
            'timezone' => null,
            'timezone_source' => null,
            'locale' => null,
            'locale_source' => null,
            'agent' => null,
        ];
    }

    public function online(): self
    {
        return $this->state(fn (): array => [
            'last_seen_at' => now(),
            'is_online' => true,
        ]);
    }

    public function offline(): self
    {
        return $this->state(fn (): array => [
            'last_seen_at' => now()->subDay(),
            'is_online' => false,
        ]);
    }
}
