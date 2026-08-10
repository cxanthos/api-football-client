# Changelog

All notable changes to this project are documented here. Format based on
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/); see [README.md](README.md#versioning) for how
this project applies semantic versioning before `1.0.0`.

## [Unreleased]

Nothing yet.

## [0.1.0] - 2026-08-09

Initial release.

### Added
- `Client` with resource-oriented methods for `countries()`, `leagues()`, `teams()`, `venues()`,
  `fixtures()`, `standings()`, `players()`, `coachs()`, `transfers()`, `trophies()`, `account()` — full
  coverage of the MVP tier plus everything originally deferred to Tier 2.
- Fully typed, readonly DTOs for every response shape, down to the leaf level.
- `Result<T>` for API-level errors (HTTP 200 with a populated `errors` field) — only transport/HTTP
  failures throw (`Exception\TransportException`).
- Rate-limit parsing (`Client::rateLimit()`) on every call.
- Manual, opt-in coverage helper (`leagues()->coverage()`).
- Opt-in throttling PSR-18 decorator (`Http\ThrottlingClient`) — respects the per-minute limit learned from
  response headers, adds a cooperative delay once the daily quota runs low, never retries a `429`.
- Optional PSR-3 logging, wired centrally in `Http\Transport` — off by default, never logs the API key.
- PSR-18/17 HTTP transport via `php-http/discovery` — no hard dependency on any specific HTTP client.

[Unreleased]: https://github.com/cxanthos/api-football-client/compare/v0.1.0...main
[0.1.0]: https://github.com/cxanthos/api-football-client/releases/tag/v0.1.0
