<?php

declare(strict_types=1);

use Syriable\UserContext\Tests\Fixtures\User;
use Syriable\UserContext\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

/**
 * Create a persisted test user.
 *
 * @param  array<string, mixed>  $attributes
 */
function makeUser(array $attributes = []): User
{
    return User::query()->create($attributes + [
        'name' => 'Test User',
        'email' => 'user'.random_int(1, PHP_INT_MAX).'@example.com',
    ]);
}
