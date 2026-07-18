<?php

declare(strict_types=1);

namespace Syriable\UserContext\Support;

final class IpAddress
{
    public static function isValid(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Whether the address belongs to a private or reserved range
     * (loopback, RFC 1918, link-local, …) that no public geolocation
     * provider can resolve.
     */
    public static function isPrivate(string $ip): bool
    {
        if (! self::isValid($ip)) {
            return true;
        }

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        ) === false;
    }

    /**
     * Zero the host part of the address: IPv4 keeps the /24 network,
     * IPv6 keeps the /48 prefix.
     */
    public static function anonymize(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            $packed = inet_pton($ip);

            if ($packed === false) {
                return $ip;
            }

            $masked = substr($packed, 0, 3)."\0";
            $result = inet_ntop($masked);

            return $result === false ? $ip : $result;
        }

        $packed = inet_pton($ip);

        if ($packed === false) {
            return $ip;
        }

        $masked = substr($packed, 0, 6).str_repeat("\0", 10);
        $result = inet_ntop($masked);

        return $result === false ? $ip : $result;
    }

    /**
     * Deterministic HMAC-SHA256 of the address, keyed with the application
     * key — comparable across requests without being reversible.
     */
    public static function hash(string $ip): string
    {
        $key = config('user-context.ip.hash_key') ?? config('app.key', '');

        return hash_hmac('sha256', $ip, is_string($key) ? $key : '');
    }
}
