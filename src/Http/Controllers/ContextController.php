<?php

declare(strict_types=1);

namespace Syriable\UserContext\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Syriable\UserContext\Http\Resources\UserContextResource;
use Syriable\UserContext\UserContextManager;

/**
 * Returns the authenticated user's own context as JSON.
 *
 * @internal
 */
final readonly class ContextController
{
    public function __invoke(Request $request, UserContextManager $manager): UserContextResource
    {
        $user = $request->user();

        abort_unless($user instanceof Model, 401);

        return new UserContextResource($manager->contextFor($user));
    }
}
