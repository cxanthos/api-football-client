# api-football-client

A typed PHP SDK for [API-Football v3](https://www.api-football.com/documentation-v3) — a resource-oriented
client with fully typed DTOs, PSR-18/17 HTTP transport, and no hidden magic (no built-in caching, retries, or
auto-throttling).

**Status: pre-release / design phase.** No tagged version yet. The API surface below is the planned MVP and
may still change before `v0.1.0`.

## Requirements

- PHP `^8.4 || ^8.5`

## Installation

```bash
composer require cxanthos/api-football-client
```

*(Not published to Packagist yet — this will work once the first tag is cut.)*

## Planned usage

```php
$client = new ApiFootball\Client(apiKey: '...');

$client->leagues()->list(country: 'england');
$client->fixtures()->events(fixture: 239625);
$client->players()->topScorers(league: 39, season: 2023);
$client->teams()->statistics(league: 39, season: 2023, team: 33);
```

## MVP endpoint coverage

| Resource | Covers |
|---|---|
| Countries, Leagues | Reference data, season coverage flags |
| Teams | Team identity + embedded venue, season statistics |
| Fixtures | Match results, goals/cards/subs (events), head-to-head history |
| Standings | League tables |
| Players | Player+season stats, squads, top scorers/assists/yellow/red cards |
| Coaches, Transfers, Trophies | Manager careers, transfer history, honours |
| Account | Free quota check (`/status`), doesn't count against your daily limit |

Deliberately **not** in scope: betting odds, match predictions, live-availability data (injuries/sidelined),
ETL/sync tooling, question-generation logic, caching, and built-in retries. Full rationale for what's in,
deferred, or excluded lives in the project's internal design notes (not published in this repo).

## Design principles

- **Resource-oriented API** matching API-Football's own vocabulary (`fixtures()`, `players()`, `standings()`, …)
- **Fully typed, readonly DTOs** — no `array`/`stdClass` payloads for MVP endpoints
- **PSR-18 + PSR-17** via `php-http/discovery` — no hard dependency on Guzzle or any specific HTTP client
- **Honest error model**: transport/HTTP failures throw; API-level errors (HTTP 200 with a populated
  `errors` field) return a `Result`-style outcome instead of throwing
- **Rate limits always exposed**, throttling is strictly **opt-in**, and a `429` is never auto-retried
- **Manual, opt-in coverage checks** — no resource method silently calls `/leagues` first to guard itself;
  that would double the cost of every call it protects

## License

MIT — see [LICENSE](LICENSE).

## Contributing

Not yet open for contributions while the MVP surface is still settling. Watch the repo if you're interested.
