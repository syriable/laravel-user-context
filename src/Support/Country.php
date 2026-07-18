<?php

declare(strict_types=1);

namespace Syriable\UserContext\Support;

use Locale;

final class Country
{
    /**
     * Resolve a human-readable country name from an ISO 3166-1 alpha-2
     * code using the intl extension. Returns null when intl is missing
     * or the code is unknown — country names are never stored.
     */
    public static function name(?string $code, ?string $displayLocale = null): ?string
    {
        if ($code === null || strlen($code) !== 2) {
            return null;
        }

        if (! class_exists(Locale::class)) {
            return null;
        }

        $code = strtoupper($code);
        $name = Locale::getDisplayRegion('-'.$code, $displayLocale ?? app()->getLocale());

        if ($name === false || $name === '' || $name === $code) {
            return null;
        }

        return $name;
    }
}
