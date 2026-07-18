# Laravel User Context

[![Latest Version on Packagist](https://img.shields.io/packagist/v/syriable/laravel-user-context.svg?style=flat-square)](https://packagist.org/packages/syriable/laravel-user-context)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/syriable/laravel-user-context/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/syriable/laravel-user-context/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/syriable/laravel-user-context.svg?style=flat-square)](https://packagist.org/packages/syriable/laravel-user-context)

> Know who is **online**, **where** they are, and **what time it is for them** — presence, timezone awareness, locale context and login metadata for any Laravel app.

A lightweight, database-driven foundation package. No Redis, no queue worker required,
no heavy dependencies — it runs on shared hosting and scales to millions of users.

```php
$user->presence()->isOnline();          // true
$user->presence()->lastSeen();           // CarbonImmutable
$user->location()->country();            // "Sweden"
$user->timezone()->currentTime();        // 15:30 local
$user->greeting();                       // "Good afternoon"

// A user in New York checking the best time to message a user in Shanghai:
$comparison = $newYorkUser->timeFor($shanghaiUser);
$comparison->formattedOffset();          // "+12:00"
$comparison->isNight();                  // true  → maybe wait
$comparison->isConvenientTime();         // false → not a good time to ping
```

## Requirements

- PHP 8.3+
- Laravel 11, 12 or 13

## Installation

```bash
composer require syriable/laravel-user-context
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="laravel-user-context-migrations"
php artisan migrate
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag="laravel-user-context-config"
```

Add the trait to your authenticatable model:

```php
use Syriable\UserContext\Concerns\HasUserContext;

class User extends Authenticatable
{
    use HasUserContext;
}
```

Register the tracking middleware so activity is recorded on each request. In
`bootstrap/app.php` (Laravel 11+):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \Syriable\UserContext\Http\Middleware\TrackUserContext::class,
    ]);
})
```

That's it — logins, logouts, presence, locale and (optionally) geolocation are now
tracked automatically.

## Documentation

- **[Configuration](docs/configuration.md)** — every config key, its default and effect.
- **[Usage](docs/usage.md)** — presence, timezones, user-to-user comparison, Blade, API.
- **[Extending](docs/extending.md)** — custom geolocation drivers, events, swapping models.
- **[Architecture Decisions](docs/adr.md)** — why the package is built the way it is.

## Usage at a glance

### Presence

```php
$user->isOnline();                       // bool
$user->presence()->status();             // "online" | "offline"
$user->presence()->lastSeen();           // ?CarbonImmutable
$user->presence()->lastLogin();          // ?CarbonImmutable

UserContext::online()->count();          // query builder over online users
```

Keep a browser tab "online" with the bundled heartbeat component:

```blade
<x-user-context::heartbeat />
```

### Timezone & locale

```php
$user->timezone()->name();               // "Asia/Shanghai" (or null if unknown)
$user->localTime();                      // CarbonImmutable in their timezone
$user->isNight();                        // bool
$user->greeting();                       // localized "Good evening"
$user->locale();                         // "en_US"

// Explicit overrides always win over IP / header detection:
UserContext::overrideTimezone($user, 'Europe/Berlin');
UserContext::overrideLocale($user, 'de');
```

### Location

```php
$user->location()->countryCode();        // "SE"
$user->location()->countryName();        // "Sweden"
$user->location()->city();               // "Stockholm"
```

### User-to-user time comparison

```php
$c = $userA->timeFor($userB);

$c->theirTime;            // CarbonImmutable in B's timezone
$c->formattedOffset();   // "+12:00"
$c->dayPeriod;           // DayPeriod::Night
$c->isConvenientTime();  // is it a reasonable local hour to contact B?
```

### Blade components

```blade
<x-user-context::user-presence :user="$user" />
<x-user-context::local-time :user="$user" format="H:i" />
<x-user-context::heartbeat />
```

### API

`GET /user-context/me` returns the authenticated user's context:

```json
{
    "online": true,
    "last_seen": "2026-07-18T10:30:00+00:00",
    "timezone": "Europe/Stockholm",
    "local_time": "15:30",
    "country": "Sweden",
    "locale": "sv_SE"
}
```

## Events

Listen for any of: `UserOnline`, `UserOffline`, `UserLocationUpdated`,
`UserTimezoneChanged`, `UserLoginRecorded`. See [Extending](docs/extending.md).

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for what has changed recently.

## Credits

- [syriable](https://github.com/syriable)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
