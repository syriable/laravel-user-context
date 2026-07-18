<?php

declare(strict_types=1);

namespace Syriable\UserContext\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Syriable\UserContext\Actions\RecordUserActivity;
use Throwable;

/**
 * Touches the authenticated user's presence on every request (throttled
 * to one write per activity window). Tracking must never break a page:
 * failures are reported, not thrown.
 */
final readonly class TrackUserContext
{
    public function __construct(private RecordUserActivity $recordActivity) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof Model) {
            try {
                $acceptLanguage = $request->header('Accept-Language');

                ($this->recordActivity)(
                    $user,
                    ip: $request->ip(),
                    userAgent: $request->userAgent(),
                    acceptLanguage: is_string($acceptLanguage) ? $acceptLanguage : null,
                );
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return $next($request);
    }
}
