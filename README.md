# api-football-client

[![CI](https://github.com/cxanthos/api-football-client/actions/workflows/ci.yml/badge.svg)](https://github.com/cxanthos/api-football-client/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A typed PHP SDK for [API-Football v3](https://www.api-football.com/documentation-v3) — a resource-oriented
client with fully typed DTOs, PSR-18/17 HTTP transport, and no hidden magic (no built-in caching, retries, or
auto-throttling).

**Status:** [`v0.1.0`](https://github.com/cxanthos/api-football-client/releases/tag/v0.1.0) — all 19 MVP
endpoints plus everything originally deferred to "Tier 2" are implemented and tested against both PHP 8.4
and 8.5. Still `0.x`: see [Versioning](#versioning) before depending on this for anything serious.

## Requirements

- PHP `^8.4 || ^8.5`

## Installation

```bash
composer require cxanthos/api-football-client
```

## Usage

```php
$client = new ApiFootball\Client(apiKey: '...');

$result = $client->countries()->list(name: 'england');

if ($result->isOk()) {
    foreach ($result->unwrap() as $country) {
        echo $country->name; // England
    }
} else {
    // API-level error (bad params, etc.) — never an exception, see "Design principles" below
    print_r($result->errors());
}
```

See **[USAGE.md](USAGE.md)** for a worked example of every resource, plus the handful of behaviors worth
knowing about (which parameters are actually enforced and why, the `standings()` footgun, pagination on
`players()->statistics()`, etc.).

## Endpoint coverage

29 of the API's ~39 total paths — every MVP endpoint plus everything originally deferred to "Tier 2." See
**[USAGE.md](USAGE.md)** for the exact method list with examples; summary by resource:

| Resource | Covers |
|---|---|
| Countries | Reference data |
| Leagues | List, seasons, coverage flags |
| Teams | Identity + embedded venue, season statistics, seasons list, countries list |
| Venues | Dedicated venue search (id/name/city/country) |
| Fixtures | Results, events, head-to-head, rounds, lineups, match statistics, per-player match stats |
| Standings | League tables |
| Players | Season stats, squads, top scorers/assists/yellow/red cards, profiles, seasons, career path |
| Coaches, Transfers, Trophies | Manager careers, transfer history, honours |
| Account | Free quota check (`/status`), doesn't count against your daily limit |

Deliberately **not** in scope, and not planned: betting odds, match predictions, live-availability data
(injuries/sidelined), ETL/sync tooling, caching, and built-in retries. Full rationale for what's in or
excluded lives in the project's internal design notes (not published in this repo).

## Design principles

- **Resource-oriented API** matching API-Football's own vocabulary (`fixtures()`, `players()`, `standings()`, …)
- **Fully typed, readonly DTOs** down to the leaf level — no `array`/`stdClass` payloads
- **PSR-18 + PSR-17** via `php-http/discovery` — no hard dependency on Guzzle or any specific HTTP client
- **Honest error model**: transport/HTTP failures throw (`Exception\TransportException`); API-level errors
  (HTTP 200 with a populated `errors` field) return a `Result`-style outcome instead of throwing
- **Rate limits always exposed** via `Client::rateLimit()`; throttling is strictly **opt-in** via
  `Http\ThrottlingClient` (see [USAGE.md](USAGE.md#throttling-opt-in)), and a `429` is never auto-retried
  under any configuration. `Result::errorId()` also tells you directly when a `Result` came from a `429`
  (`ErrorId::RateLimited`), rather than requiring you to infer it from `rateLimit()` after the fact
- **Manual, opt-in coverage checks** — `leagues()->coverage()` is a plain, explicit call; no resource method
  silently calls `/leagues` first to guard itself, which would double the cost of every call it protects
- **No invented requirements** — a parameter is only enforced client-side when the live API spec or docs
  text actually says it's required; everything else passes through as-is and lets the API's own response
  speak for itself
- **Optional PSR-3 logging** — off by default; pass a logger and get requests, API-level errors, and
  rate-limit warnings for free on every call (see [USAGE.md](USAGE.md#logging-optional)). The API key is a
  header, never logged, by construction — not by careful omission

## Development

```bash
composer install
composer test       # PHPUnit
composer analyse     # PHPStan, max level
composer cs           # PHP-CS-Fixer, dry-run
composer cs-fix       # PHP-CS-Fixer, auto-fix
```

If you have multiple PHP versions installed side by side (e.g. `php8.4`, `php8.5`), invoke the test/analysis
binaries directly rather than through `composer`, since Composer's script runner resolves `vendor/bin/*` via
its own shebang line, not whichever `php` ran Composer itself:

```bash
php8.5 vendor/bin/phpunit
php8.5 vendor/bin/phpstan analyse
```

## Versioning

Not yet at `1.0.0` — treat every `0.x` release as pre-release. Once `1.0.0` ships, this follows standard
[Semantic Versioning](https://semver.org/) and only major versions may break compatibility. Until then:

- **Never considered breaking** (safe in a patch or minor release): adding a new resource, method, DTO
  property, or query parameter; widening what a method accepts.
- **Considered breaking**, and will be called out in [CHANGELOG.md](CHANGELOG.md): removing or renaming a
  public method or DTO property, changing a DTO property's type, or changing what a method requires.

Read the changelog before upgrading a minor version while still on `0.x`.

## License

MIT — see [LICENSE](LICENSE).

## Contributing

Not yet open for contributions while the MVP surface is still settling. Watch the repo if you're interested.
