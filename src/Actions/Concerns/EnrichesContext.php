<?php

declare(strict_types=1);

namespace Syriable\UserContext\Actions\Concerns;

use Illuminate\Support\Str;
use Syriable\UserContext\Enums\ContextSource;
use Syriable\UserContext\Models\UserContext;
use Syriable\UserContext\Support\AcceptLanguage;

/**
 * Shared enrichment applied whenever activity or a login is recorded.
 *
 * @internal
 */
trait EnrichesContext
{
    private function detectLocale(UserContext $context, ?string $acceptLanguage): void
    {
        if (! (bool) config('user-context.locale.detect_from_header', true)) {
            return;
        }

        if ($acceptLanguage === null || $context->locale_source === ContextSource::User) {
            return;
        }

        $supported = config('user-context.locale.supported', []);

        /** @var array<int, string> $supported */
        $supported = is_array($supported) ? array_values(array_filter($supported, is_string(...))) : [];

        $locale = AcceptLanguage::parse($acceptLanguage, $supported);

        if ($locale === null || $locale === $context->locale) {
            return;
        }

        $context->locale = $locale;
        $context->locale_source = ContextSource::Header;
    }

    private function collectAgent(UserContext $context, ?string $userAgent): void
    {
        if (! (bool) config('user-context.agent.collect', false) || $userAgent === null) {
            return;
        }

        $context->agent = ['user_agent' => Str::limit($userAgent, 500, '')];
    }

    private function agentForRecord(?string $userAgent): ?string
    {
        if (! (bool) config('user-context.agent.collect', false) || $userAgent === null) {
            return null;
        }

        return Str::limit($userAgent, 500, '');
    }
}
