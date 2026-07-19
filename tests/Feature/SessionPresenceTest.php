<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Syriable\UserContext\Contracts\PresenceSource;
use Syriable\UserContext\Models\UserContext as UserContextModel;
use Syriable\UserContext\Presence\SessionPresenceSource;
use Syriable\UserContext\Presence\TablePresenceSource;

/**
 * Creates the `sessions` table Laravel ships with the database driver.
 */
function createSessionsTable(): void
{
    Schema::create('sessions', function (Blueprint $table): void {
        $table->string('id')->primary();
        $table->foreignId('user_id')->nullable()->index();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    });
}

/**
 * @param  array<string, mixed>  $attributes
 */
function insertSession(int $userId, int $lastActivity, array $attributes = []): void
{
    DB::table('sessions')->insert($attributes + [
        'id' => 'sess_'.$userId.'_'.$lastActivity,
        'user_id' => $userId,
        'ip_address' => '8.8.8.8',
        'user_agent' => 'PestBrowser/1.0',
        'payload' => 'x',
        'last_activity' => $lastActivity,
    ]);
}

describe('session presence source selection', function (): void {
    it('resolves the sessions source in auto mode when the database driver and table are present', function (): void {
        createSessionsTable();
        config()->set('session.driver', 'database');
        config()->set('user-context.presence.source', 'auto');

        expect(app(PresenceSource::class))->toBeInstanceOf(SessionPresenceSource::class);
    });

    it('falls back to the table source in auto mode on a non-database driver', function (): void {
        config()->set('session.driver', 'array');
        config()->set('user-context.presence.source', 'auto');

        expect(app(PresenceSource::class))->toBeInstanceOf(TablePresenceSource::class);
    });

    it('falls back to the table source in auto mode when the sessions table is missing', function (): void {
        config()->set('session.driver', 'database');
        config()->set('user-context.presence.source', 'auto');

        expect(app(PresenceSource::class))->toBeInstanceOf(TablePresenceSource::class);
    });

    it('honors an explicit table source even on the database driver', function (): void {
        createSessionsTable();
        config()->set('session.driver', 'database');
        config()->set('user-context.presence.source', 'table');

        expect(app(PresenceSource::class))->toBeInstanceOf(TablePresenceSource::class);
    });
});

describe('presence read from the sessions table', function (): void {
    beforeEach(function (): void {
        createSessionsTable();
        config()->set('session.driver', 'database');
        config()->set('user-context.presence.source', 'sessions');
        config()->set('user-context.online_timeout', 300);
    });

    it('reports a user with recent session activity as online', function (): void {
        $user = makeUser();
        insertSession($user->getKey(), CarbonImmutable::now()->getTimestamp());

        expect($user->isOnline())->toBeTrue()
            ->and($user->presence()->status())->toBe('online');
    });

    it('reports a user whose session activity is beyond the timeout as offline', function (): void {
        $user = makeUser();
        insertSession($user->getKey(), CarbonImmutable::now()->subMinutes(10)->getTimestamp());

        expect($user->isOnline())->toBeFalse()
            ->and($user->presence()->status())->toBe('offline');
    });

    it('reports a user with no session row as offline', function (): void {
        $user = makeUser();

        expect($user->isOnline())->toBeFalse()
            ->and($user->presence()->lastSeen())->toBeNull();
    });

    it('reads last-seen, ip and user agent from the sessions table', function (): void {
        $user = makeUser();
        $now = CarbonImmutable::now()->startOfSecond();
        insertSession($user->getKey(), $now->getTimestamp());

        expect($user->presence()->lastSeen()->getTimestamp())->toBe($now->getTimestamp())
            ->and($user->location()->ipAddress())->toBe('8.8.8.8')
            ->and($user->location()->userAgent())->toBe('PestBrowser/1.0');
    });

    it('reads presence from sessions even without a user_contexts row', function (): void {
        $user = makeUser();
        insertSession($user->getKey(), CarbonImmutable::now()->getTimestamp());

        expect(UserContextModel::query()->count())->toBe(0)
            ->and($user->isOnline())->toBeTrue();
    });

    it('uses the most recent session when a user has several devices', function (): void {
        $user = makeUser();
        insertSession($user->getKey(), CarbonImmutable::now()->subMinutes(10)->getTimestamp(), [
            'ip_address' => '1.1.1.1',
        ]);
        insertSession($user->getKey(), CarbonImmutable::now()->getTimestamp(), [
            'ip_address' => '8.8.8.8',
        ]);

        expect($user->isOnline())->toBeTrue()
            ->and($user->location()->ipAddress())->toBe('8.8.8.8');
    });
});
