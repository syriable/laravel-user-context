<?php

declare(strict_types=1);

namespace Syriable\UserContext\Support;

final class AcceptLanguage
{
    /**
     * Parse an Accept-Language header into the best matching locale,
     * normalized to underscore form (e.g. "en-us;q=0.8" → "en_US").
     *
     * @param  array<int, string>  $supported  restrict matching to these locales; empty accepts any
     */
    public static function parse(?string $header, array $supported = []): ?string
    {
        if ($header === null || trim($header) === '') {
            return null;
        }

        $candidates = [];

        foreach (explode(',', $header) as $part) {
            $segments = explode(';', trim($part));
            $tag = trim($segments[0]);

            if ($tag === '' || $tag === '*') {
                continue;
            }

            $quality = 1.0;

            foreach (array_slice($segments, 1) as $parameter) {
                if (preg_match('/^\s*q\s*=\s*([0-9.]+)\s*$/i', $parameter, $matches) === 1) {
                    $quality = (float) $matches[1];
                }
            }

            $normalized = self::normalize($tag);

            if ($normalized !== null) {
                $candidates[] = ['locale' => $normalized, 'quality' => $quality];
            }
        }

        usort($candidates, fn (array $a, array $b): int => $b['quality'] <=> $a['quality']);

        if ($supported === []) {
            return $candidates[0]['locale'] ?? null;
        }

        $normalizedSupported = array_filter(array_map(self::normalize(...), $supported));

        foreach ($candidates as $candidate) {
            foreach ($normalizedSupported as $locale) {
                if (
                    strcasecmp($candidate['locale'], $locale) === 0
                    || strcasecmp(self::language($candidate['locale']), self::language($locale)) === 0
                ) {
                    return $locale;
                }
            }
        }

        return null;
    }

    /**
     * The primary language subtag of a locale ("en_US" → "en").
     */
    public static function language(string $locale): string
    {
        $primary = preg_split('/[_-]/', $locale)[0] ?? $locale;

        return strtolower($primary);
    }

    private static function normalize(string $tag): ?string
    {
        if (preg_match('/^([a-z]{2,8})(?:[-_]([a-z0-9]{2,8}))?$/i', trim($tag), $matches) !== 1) {
            return null;
        }

        $language = strtolower($matches[1]);

        if (! isset($matches[2])) {
            return $language;
        }

        return $language.'_'.strtoupper($matches[2]);
    }
}
