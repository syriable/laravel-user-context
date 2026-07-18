<?php

declare(strict_types=1);

namespace Syriable\UserContext\Enums;

use Syriable\UserContext\Support\IpAddress;

enum IpPrivacyMode: string
{
    case Store = 'store';
    case Anonymize = 'anonymize';
    case Hash = 'hash';
    case Discard = 'discard';

    public static function configured(): self
    {
        $mode = config('user-context.ip.privacy', self::Store->value);

        return is_string($mode) ? (self::tryFrom($mode) ?? self::Store) : self::Store;
    }

    /**
     * Transform a raw IP address into its storable representation.
     */
    public function apply(?string $ip): ?string
    {
        if ($ip === null || ! IpAddress::isValid($ip)) {
            return null;
        }

        return match ($this) {
            self::Store => $ip,
            self::Anonymize => IpAddress::anonymize($ip),
            self::Hash => IpAddress::hash($ip),
            self::Discard => null,
        };
    }
}
