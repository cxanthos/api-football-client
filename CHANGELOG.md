# Changelog

All notable changes to this project are documented here. Format based on
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/); see [README.md](README.md#versioning) for how
this project applies semantic versioning before `1.0.0`.

## [Unreleased]

Nothing yet.

## [0.2.0] - 2026-08-14

### Added
- `Result::errorId()`, returning `?ErrorId` — currently just `ErrorId::RateLimited`, set when a `Result`
  came from an HTTP 429 response. Lets callers detect rate-limiting directly instead of inferring it from
  `Client::rateLimit()` after the fact. Deliberately narrow: other API-level errors (bad params, auth) aren't
  reliably classifiable from status code on this API, so `errorId()` stays `null` for those — use `errors()`.

### Documentation
- Clarified that `leagues()->list()`'s `name` param is an exact match and `search` is partial/fuzzy — this
  wasn't documented anywhere and caused a silent empty-result footgun for a first-time caller.

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

[Unreleased]: https://github.com/cxanthos/api-football-client/compare/v0.2.0...main
[0.2.0]: https://github.com/cxanthos/api-football-client/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/cxanthos/api-football-client/releases/tag/v0.1.0
