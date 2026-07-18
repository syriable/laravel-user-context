<?php

namespace Syriable\UserContext\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Syriable\UserContext\UserContext
 */
class UserContext extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Syriable\UserContext\UserContext::class;
    }
}
