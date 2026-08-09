# Usage

Every example below assumes:

```php
$client = new ApiFootball\Client(apiKey: '...');
```

If you don't pass an HTTP client/factory explicitly, `php-http/discovery` finds whatever PSR-18
implementation your project already has installed (Guzzle, Symfony HttpClient, etc.).

## Working with results

Every resource method returns a `Result<T>` — never an exception for a normal API-level error (bad
parameter, unknown id, ...). Only transport/HTTP failures (`Exception\TransportException`) throw.

```php
$result = $client->countries()->list(name: 'england');

if ($result->isOk()) {
    $countries = $result->unwrap(); // list<DTO\Country>
} else {
    $result->errors(); // array<string,string>
}
```

`errors()` is always a flat `array<string,string>` regardless of what the API actually sent back — the raw
shape varies (confirmed against the live API): an invalid key comes back as
`['token' => 'Invalid API key, please check your request and credentials.', 'error' => '4xSe']`, a bad
parameter as `['season' => 'The Season field must be at least 4 characters in length.']`. Don't assume a
specific key exists (like `'message'`) — iterate the map or check for the one key you're expecting.

Rate-limit standing from the most recent call is always available, regardless of the result:

```php
$client->rateLimit(); // ?RateLimit — null before the first call
```

---

## Countries

```php
$client->countries()->list(name: 'england');
```

No parameters are required or enforced — call with none to get the full list.

## Leagues

```php
$leagues = $client->leagues()->list(country: 'england')->unwrap();
$years = $client->leagues()->seasons()->unwrap(); // list<int>

$coverage = $client->leagues()->coverage(id: 39, season: 2023)->unwrap();
if ($coverage->supports('top_assists')) {
    // safe to call players()->topAssists() for this league+season
}
```

`coverage()` is a plain, explicit, separately-billed call — nothing else in the SDK invokes it for you.

## Teams

```php
$teams = $client->teams()->list(league: 39, season: 2023)->unwrap();
$stats = $client->teams()->statistics(league: 39, season: 2023, team: 33)->unwrap();
$years = $client->teams()->seasons(team: 33)->unwrap();       // list<int>
$countries = $client->teams()->countries()->unwrap();          // list<DTO\Country> — same shape as countries()
```

`statistics()`'s three parameters are all required by the API itself (schema-enforced) — the method
signature has no defaults for them.

## Venues

```php
$client->venues()->list(city: 'Manchester');
```

A dedicated resource (Tier 2) on top of `/teams`' embedded venue — adds `country` and search by
city/country/name directly. Shares the same `DTO\Venue` class as `teams()->list()`'s embedded venue.

## Fixtures

```php
$recent = $client->fixtures()->list(team: 33, last: 5)->unwrap();
$events = $client->fixtures()->events(fixture: 239625)->unwrap();
$h2h = $client->fixtures()->headToHead(h2h: '33-34')->unwrap();

$fixture = $recent[0];
$fixture->fixture->status->code;  // FixtureStatusCode enum, or null if the API returns an unrecognized code
$fixture->fixture->status->raw;   // the raw short code either way — never throws on an unknown value
```

`list()` has no enforced parameters — pass whatever filters you need (`league`+`season`, `date`, `team`, ...).

Tier 2 additions — richer, but per-match cost like `events()`:

```php
$rounds = $client->fixtures()->rounds(league: 39, season: 2023)->unwrap();     // list<string>
$lineups = $client->fixtures()->lineups(fixture: 239625)->unwrap();
$stats = $client->fixtures()->statistics(fixture: 239625)->unwrap();
$players = $client->fixtures()->players(fixture: 239625)->unwrap();            // richest, most expensive
```

`rounds()` is only verified for the default shape (no `dates` param) — passing `dates: true` is documented
to change the response shape, which hasn't been observed, so it isn't specially handled.
`statistics()`'s `StatItem::$value` stays `int|string|null` exactly as the API sends it (counts are ints,
things like possession are percentage strings, untracked stats are null) — nothing is coerced.

## Standings

```php
$tables = $client->standings()->list(season: 2023, league: 39)->unwrap();

foreach ($tables[0]->standings[0] as $row) {
    echo "{$row->rank}. {$row->team->name} — {$row->points} pts\n";
}
```

`standings` is a list of *groups* (`standings[0]` is usually "the table"; a competition with a group stage
has more than one). Passing `season` with neither `league` nor `team` is legal but fetches every league's
table for that season at once — pass one of them unless that's really what you want.

## Players

```php
$stats = $client->players()->statistics(id: 276, season: 2023)->unwrap();
$squad = $client->players()->squads(team: 33)->unwrap();
$topScorers = $client->players()->topScorers(league: 39, season: 2023)->unwrap();
// topAssists(), topYellowCards(), topRedCards() take the same (league, season) and return the same shape
```

`squads()` throws `InvalidArgumentException` if you pass neither `team` nor `player` — the one guard in
the whole SDK that's enforced without being schema-required, because the API's own docs say so explicitly.
`statistics()` is paginated (`page` param) with no auto-pagination helper — you own the loop and the quota.

Tier 2 additions:

```php
$profiles = $client->players()->profiles(player: 276)->unwrap();  // DTO\ProfiledPlayer — has number/position, no injured
$years = $client->players()->seasons(player: 276)->unwrap();       // list<int>
$career = $client->players()->teams(player: 276)->unwrap();        // "which clubs has X played for", by season
```

`profiles()` returns a genuinely different player shape from `statistics()`/`topScorers()` etc. — don't
assume the fields line up.

## Coachs

```php
$client->coachs()->list(team: 85);
```

Method name matches the API path exactly (`coachs`, not "coaches").

## Transfers

```php
$client->transfers()->list(player: 276);
```

## Trophies

```php
$client->trophies()->list(player: 276);
```

## Throttling (opt-in)

Nothing throttles by default. If you want it, wrap your own PSR-18 client with `ThrottlingClient` and pass
the result as `Client`'s `$httpClient`:

```php
use ApiFootball\Http\ThrottlingClient;
use Http\Discovery\Psr18ClientDiscovery;

$client = new ApiFootball\Client(
    apiKey: '...',
    httpClient: new ThrottlingClient(Psr18ClientDiscovery::find()),
);
```

It respects the per-minute limit (learned from the `X-RateLimit-Limit` response header — nothing is
hardcoded, so it adapts to whatever plan you're on) and, once the daily quota drops to 50 remaining or
fewer by default, adds a small extra delay after each call to spread out what's left of the day. Both
thresholds are constructor parameters if the defaults don't fit:

```php
new ThrottlingClient(
    inner: $myPsr18Client,
    lowDailyRemainingThreshold: 200,
    lowDailyRemainingDelaySeconds: 3.0,
);
```

It never retries. A `429` (or any other status) passes straight through unchanged — this only ever delays
*before* sending a request, never reacts to one that already failed.

## Logging (optional)

Off by default — pass a PSR-3 logger and every call logs for free, with no changes needed to any resource
call:

```php
$client = new ApiFootball\Client(apiKey: '...', logger: $myPsr3Logger);
```

| Level | When |
|---|---|
| `debug` | Every outgoing request (method + URI, never headers) and every successful response (endpoint + result count) |
| `warning` | API-level errors (HTTP 200 with a populated `errors` field), daily quota ≤ 10 remaining, per-minute limit exhausted |
| `error` | Transport/HTTP failures, right before the `TransportException` is thrown |

The API key is sent as a header (`x-apisports-key`), never as part of the URL — so it can never end up in
a log line by construction, not by careful omission. No other request/response headers are logged either.

## Account

```php
$status = $client->account()->status()->unwrap();
echo "{$status->requests->current}/{$status->requests->limitDay} requests used today";
```

Free — does not count against your daily quota.
