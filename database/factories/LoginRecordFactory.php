<?php

declare(strict_types=1);

namespace Syriable\UserContext\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Syriable\UserContext\Models\LoginRecord;

/**
 * @extends Factory<LoginRecord>
 */
final class LoginRecordFactory extends Factory
{
    protected $model = LoginRecord::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_type' => 'user',
            'user_id' => $this->faker->numberBetween(1, 1_000_000),
            'ip_address' => $this->faker->ipv4(),
            'country_code' => null,
            'city' => null,
            'timezone' => null,
            'user_agent' => null,
            'logged_in_at' => now(),
            'logged_out_at' => null,
        ];
    }
}
