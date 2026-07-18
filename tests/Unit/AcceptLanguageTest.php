<?php

declare(strict_types=1);

use Syriable\UserContext\Support\AcceptLanguage;

describe('AcceptLanguage::parse', function (): void {
    it('returns null for an empty header', function (): void {
        expect(AcceptLanguage::parse(null))->toBeNull()
            ->and(AcceptLanguage::parse(''))->toBeNull();
    });

    it('normalizes a simple tag to underscore form', function (): void {
        expect(AcceptLanguage::parse('en-US'))->toBe('en_US');
    });

    it('picks the highest quality candidate', function (): void {
        expect(AcceptLanguage::parse('fr;q=0.5, en;q=0.9, de;q=0.1'))->toBe('en');
    });

    it('defaults quality to 1.0 when omitted', function (): void {
        expect(AcceptLanguage::parse('nl, en;q=0.9'))->toBe('nl');
    });

    it('matches against a supported list by exact locale', function (): void {
        expect(AcceptLanguage::parse('en-US,fr;q=0.8', ['fr', 'en_US']))->toBe('en_US');
    });

    it('falls back to language-level match against the supported list', function (): void {
        expect(AcceptLanguage::parse('en-GB', ['en_US']))->toBe('en_US');
    });

    it('returns null when nothing in the supported list matches', function (): void {
        expect(AcceptLanguage::parse('ja', ['en', 'fr']))->toBeNull();
    });

    it('ignores the wildcard tag', function (): void {
        expect(AcceptLanguage::parse('*'))->toBeNull();
    });
});

describe('AcceptLanguage::language', function (): void {
    it('extracts the primary subtag', function (string $locale, string $expected): void {
        expect(AcceptLanguage::language($locale))->toBe($expected);
    })->with([
        ['en_US', 'en'],
        ['fr-CA', 'fr'],
        ['de', 'de'],
    ]);
});
