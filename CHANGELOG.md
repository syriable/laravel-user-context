# Changelog

All notable changes to `laravel-user-context` will be documented in this file.

## 1.0.3 - 2026-07-19

### What's Changed

* Reuse Laravel's sessions table for presence via a PresenceSource seam by @alkhatibsy in https://github.com/syriable/laravel-user-context/pull/4

**Full Changelog**: https://github.com/syriable/laravel-user-context/compare/1.0.2...1.0.3

## 1.0.2 - 2026-07-18

**Full Changelog**: https://github.com/syriable/laravel-user-context/compare/1.0.1...1.0.2

## 1.0.1 - 2026-07-18

### What's Changed

* fix: Blade components did not resolve via the documented :: tag syntax by @alkhatibsy in https://github.com/syriable/laravel-user-context/pull/3

**Full Changelog**: https://github.com/syriable/laravel-user-context/compare/1.0.0...1.0.1

## 1.0.0 - 2026-07-18

### What's Changed

* feat: user presence, timezone, locale and login context by @alkhatibsy in https://github.com/syriable/laravel-user-context/pull/2
* Bump actions/checkout from 6 to 7 by @dependabot[bot] in https://github.com/syriable/laravel-user-context/pull/1

### New Contributors

* @alkhatibsy made their first contribution in https://github.com/syriable/laravel-user-context/pull/2
* @dependabot[bot] made their first contribution in https://github.com/syriable/laravel-user-context/pull/1

**Full Changelog**: https://github.com/syriable/laravel-user-context/commits/1.0.0

## Unreleased

### Changed

- **Safer privacy defaults.** `ip.privacy` defaults to `anonymize`;
  `geolocation.driver` defaults to `null` (opt into `ipinfo` / `maxmind` /
  `ipapi`); `queue.enabled` defaults to `true` so geo lookups never block
  the request path; package routes include `throttle:60,1`.

### Added

- Presence tracking: online/offline detection derived from `last_seen_at` against a
  configurable timeout, activity-throttled middleware, heartbeat endpoint and Blade
  component, `online()`/`offline()` scopes, and `UserOnline`/`UserOffline` events.
- IP geolocation with a pluggable `GeolocationProvider` contract and `ipapi`,
  `ipinfo`, `maxmind` and `null` drivers, a caching decorator, and a
  queue-by-default `ResolveUserLocation` job.
- Timezone awareness: IANA-based, DST-aware `Timezone` helper with `now()`,
  `dayPeriod()`, `isNight()` and localized `greeting()`, plus user-to-user
  `TimeComparison` (`timeFor()`) with offset and convenient-time detection.
- Localization context: `Accept-Language` detection, user locale/language, and
  explicit `overrideLocale()`.
- Login metadata: append-only login ledger (`LoginRecord`), `UserLoginRecorded`
  event, and a `user-context:prune` command.
- IP privacy modes (`store` / `anonymize` / `hash` / `discard`) and private-range
  skipping.
- `HasUserContext` trait, `UserContext` facade, `ContextSnapshot` DTO,
  `UserContextResource` and a `/me` endpoint.
- `user-context:sweep-offline` command for prompt `UserOffline` events.
