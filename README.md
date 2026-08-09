# api-football-client

[![CI](https://github.com/cxanthos/api-football-client/actions/workflows/ci.yml/badge.svg)](https://github.com/cxanthos/api-football-client/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A typed PHP SDK for [API-Football v3](https://www.api-football.com/documentation-v3) — a resource-oriented
client with fully typed DTOs, PSR-18/17 HTTP transport, and no hidden magic (no built-in caching, retries, or
auto-throttling).

**Status: active development, pre-release.** No tagged version yet. Substrate (transport, envelope
decoding, error model, rate-limit parsing) is implemented, along with the first resource (`countries()`) as
the reference pattern for the rest. 18 more MVP resources are planned — see the coverage table below for
what's live vs. still on the list.

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

Everything else below is the **planned** shape once the corresponding resource exists — not yet callable:

```php
$client->leagues()->list(country: 'england');
$client->fixtures()->events(fixture: 239625);
$client->players()->topScorers(league: 39, season: 2023);
$client->teams()->statistics(league: 39, season: 2023, team: 33);
```

## MVP endpoint coverage

| Resource | Covers | Status |
|---|---|---|
| Countries | Reference data | ✅ Implemented |
| Leagues | Season coverage flags | Planned |
| Teams | Identity + embedded venue, season statistics | Planned |
| Fixtures | Match results, goals/cards/subs (events), head-to-head history | Planned |
| Standings | League tables | Planned |
| Players | Player+season stats, squads, top scorers/assists/yellow/red cards | Planned |
| Coaches, Transfers, Trophies | Manager careers, transfer history, honours | Planned |
| Account | Free quota check (`/status`), doesn't count against your daily limit | Planned |

Deliberately **not** in scope: betting odds, match predictions, live-availability data (injuries/sidelined),
ETL/sync tooling, question-generation logic, caching, and built-in retries. Full rationale for what's in,
deferred, or excluded lives in the project's internal design notes (not published in this repo).

## Design principles

- **Resource-oriented API** matching API-Football's own vocabulary (`fixtures()`, `players()`, `standings()`, …)
- **Fully typed, readonly DTOs** — no `array`/`stdClass` payloads for MVP endpoints
- **PSR-18 + PSR-17** via `php-http/discovery` — no hard dependency on Guzzle or any specific HTTP client
- **Honest error model**: transport/HTTP failures throw (`Exception\TransportException`); API-level errors
  (HTTP 200 with a populated `errors` field) return a `Result`-style outcome instead of throwing
- **Rate limits always exposed** via `Client::rateLimit()`, throttling is strictly **opt-in** (not built yet),
  and a `429` is never auto-retried
- **Manual, opt-in coverage checks** (planned, once `Leagues` exists) — no resource method silently calls
  `/leagues` first to guard itself; that would double the cost of every call it protects

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
