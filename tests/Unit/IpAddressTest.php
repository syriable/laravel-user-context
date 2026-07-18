<?php

declare(strict_types=1);

use Syriable\UserContext\Enums\IpPrivacyMode;
use Syriable\UserContext\Support\IpAddress;

describe('IpAddress', function (): void {
    it('validates IPv4 and IPv6 addresses', function (): void {
        expect(IpAddress::isValid('8.8.8.8'))->toBeTrue()
            ->and(IpAddress::isValid('2001:4860:4860::8888'))->toBeTrue()
            ->and(IpAddress::isValid('not-an-ip'))->toBeFalse();
    });

    it('detects private and reserved ranges', function (string $ip, bool $private): void {
        expect(IpAddress::isPrivate($ip))->toBe($private);
    })->with([
        ['127.0.0.1', true],
        ['10.0.0.5', true],
        ['192.168.1.1', true],
        ['8.8.8.8', false],
        ['1.1.1.1', false],
    ]);

    it('anonymizes an IPv4 address to its /24 network', function (): void {
        expect(IpAddress::anonymize('203.0.113.42'))->toBe('203.0.113.0');
    });

    it('anonymizes an IPv6 address to its /48 prefix', function (): void {
        expect(IpAddress::anonymize('2001:db8:abcd:1234::1'))->toBe('2001:db8:abcd::');
    });

    it('produces a stable, non-reversible hash', function (): void {
        config()->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));

        $first = IpAddress::hash('8.8.8.8');
        $second = IpAddress::hash('8.8.8.8');

        expect($first)->toBe($second)
            ->and($first)->not->toContain('8.8.8.8')
            ->and(strlen($first))->toBe(64);
    });
});

describe('IpPrivacyMode::apply', function (): void {
    it('stores the raw address in store mode', function (): void {
        expect(IpPrivacyMode::Store->apply('8.8.8.8'))->toBe('8.8.8.8');
    });

    it('anonymizes in anonymize mode', function (): void {
        expect(IpPrivacyMode::Anonymize->apply('8.8.8.8'))->toBe('8.8.8.0');
    });

    it('discards the address in discard mode', function (): void {
        expect(IpPrivacyMode::Discard->apply('8.8.8.8'))->toBeNull();
    });

    it('returns null for an invalid address regardless of mode', function (): void {
        expect(IpPrivacyMode::Store->apply('nonsense'))->toBeNull()
            ->and(IpPrivacyMode::Store->apply(null))->toBeNull();
    });
});
