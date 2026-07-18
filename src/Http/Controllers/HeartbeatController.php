<?php

declare(strict_types=1);

namespace Syriable\UserContext\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Syriable\UserContext\Actions\RecordUserActivity;

/**
 * Receives presence pings from the bundled JavaScript component or any
 * SPA client.
 *
 * @internal
 */
final readonly class HeartbeatController
{
    public function __invoke(Request $request, RecordUserActivity $recordActivity): JsonResponse
    {
        $user = $request->user();

        abort_unless($user instanceof Model, 401);

        $acceptLanguage = $request->header('Accept-Language');

        $recordActivity(
            $user,
            ip: $request->ip(),
            userAgent: $request->userAgent(),
            acceptLanguage: is_string($acceptLanguage) ? $acceptLanguage : null,
        );

        return new JsonResponse([
            'online' => true,
            'server_time' => CarbonImmutable::now()->toIso8601String(),
        ]);
    }
}
