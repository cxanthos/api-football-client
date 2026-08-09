# api-football-client

[![CI](https://github.com/cxanthos/api-football-client/actions/workflows/ci.yml/badge.svg)](https://github.com/cxanthos/api-football-client/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A typed PHP SDK for [API-Football v3](https://www.api-football.com/documentation-v3) — a resource-oriented
client with fully typed DTOs, PSR-18/17 HTTP transport, and no hidden magic (no built-in caching, retries, or
auto-throttling).

**Status: MVP feature-complete, pre-release.** No tagged version yet. All 19 MVP resources below are
implemented and tested against both PHP 8.4 and 8.5.

## Requirements

- PHP `^8.4 || ^8.5`

## Installation

```bash
composer require cxanthos/api-football-client
```

*(Not published to Packagist yet — this will work once the first tag is cut.)*

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

## MVP endpoint coverage

| Resource | Methods | Status |
|---|---|---|
| Countries | `list()` | ✅ Implemented |
| Leagues | `list()`, `seasons()`, `coverage()` | ✅ Implemented |
| Teams | `list()`, `statistics()` | ✅ Implemented |
| Fixtures | `list()`, `events()`, `headToHead()` | ✅ Implemented |
| Standings | `list()` | ✅ Implemented |
| Players | `statistics()`, `squads()`, `topScorers()`, `topAssists()`, `topYellowCards()`, `topRedCards()` | ✅ Implemented |
| Coachs | `list()` | ✅ Implemented |
| Transfers | `list()` | ✅ Implemented |
| Trophies | `list()` | ✅ Implemented |
| Account | `status()` — free quota check, doesn't count against your daily limit | ✅ Implemented |

Deliberately **not** in scope: betting odds, match predictions, live-availability data (injuries/sidelined),
`/fixtures/lineups`/`statistics`/`players`/`rounds`, `/venues`, ETL/sync tooling, question-generation logic,
caching, and built-in retries. Full rationale for what's in, deferred, or excluded lives in the project's
internal design notes (not published in this repo).

## Design principles

- **Resource-oriented API** matching API-Football's own vocabulary (`fixtures()`, `players()`, `standings()`, …)
- **Fully typed, readonly DTOs** down to the leaf level — no `array`/`stdClass` payloads
- **PSR-18 + PSR-17** via `php-http/discovery` — no hard dependency on Guzzle or any specific HTTP client
- **Honest error model**: transport/HTTP failures throw (`Exception\TransportException`); API-level errors
  (HTTP 200 with a populated `errors` field) return a `Result`-style outcome instead of throwing
- **Rate limits always exposed** via `Client::rateLimit()`; throttling is strictly **opt-in** via
  `Http\ThrottlingClient` (see [USAGE.md](USAGE.md#throttling-opt-in)), and a `429` is never auto-retried
  under any configuration
- **Manual, opt-in coverage checks** — `leagues()->coverage()` is a plain, explicit call; no resource method
  silently calls `/leagues` first to guard itself, which would double the cost of every call it protects
- **No invented requirements** — a parameter is only enforced client-side when the live API spec or docs
  text actually says it's required; everything else passes through as-is and lets the API's own response
  speak for itself

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

## License

MIT — see [LICENSE](LICENSE).

## Contributing

Not yet open for contributions while the MVP surface is still settling. Watch the repo if you're interested.
