<?php

declare(strict_types=1);

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->each->not->toBeUsed();

arch('strict types are declared everywhere')
    ->expect('Syriable\UserContext')
    ->toUseStrictTypes();

arch('data transfer objects are final and readonly')
    ->expect('Syriable\UserContext\Data')
    ->toBeReadonly()
    ->toBeFinal();

arch('actions are final')
    ->expect('Syriable\UserContext\Actions')
    ->toBeFinal()
    ->ignoring('Syriable\UserContext\Actions\Concerns');

arch('actions do not depend on the HTTP layer')
    ->expect('Syriable\UserContext\Actions')
    ->not->toUse('Illuminate\Http');

arch('enums are backed enums')
    ->expect('Syriable\UserContext\Enums')
    ->toBeEnums();

arch('contracts are interfaces')
    ->expect('Syriable\UserContext\Contracts')
    ->toBeInterfaces();
