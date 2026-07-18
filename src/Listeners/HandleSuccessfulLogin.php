<?php

declare(strict_types=1);

namespace Syriable\UserContext\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;
use Syriable\UserContext\Actions\RecordLogin;

/**
 * @internal
 */
final readonly class HandleSuccessfulLogin
{
    public function __construct(private RecordLogin $recordLogin) {}

    public function handle(Login $event): void
    {
        if (! $event->user instanceof Model) {
            return;
        }

        $request = request();
        $acceptLanguage = $request->header('Accept-Language');

        ($this->recordLogin)(
            $event->user,
            ip: $request->ip(),
            userAgent: $request->userAgent(),
            acceptLanguage: is_string($acceptLanguage) ? $acceptLanguage : null,
        );
    }
}
