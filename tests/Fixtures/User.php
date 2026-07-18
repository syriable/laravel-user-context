<?php

declare(strict_types=1);

namespace Syriable\UserContext\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Syriable\UserContext\Concerns\HasUserContext;

/**
 * @property int $id
 */
final class User extends Authenticatable
{
    use HasUserContext;

    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;
}
