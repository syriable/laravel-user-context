# Changelog

All notable changes to `laravel-user-context` will be documented in this file.

## Unreleased

### Added
- Presence tracking: online/offline detection derived from `last_seen_at` against a
  configurable timeout, activity-throttled middleware, heartbeat endpoint and Blade
  component, `online()`/`offline()` scopes, and `UserOnline`/`UserOffline` events.
- IP geolocation with a pluggable `GeolocationProvider` contract and `ipapi`,
  `ipinfo`, `maxmind` and `null` drivers, a caching decorator, and a queue-friendly
  (sync-by-default) `ResolveUserLocation` job.
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
