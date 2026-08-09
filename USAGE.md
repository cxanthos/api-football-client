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
```

`statistics()`'s three parameters are all required by the API itself (schema-enforced) — the method
signature has no defaults for them.

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

## Account

```php
$status = $client->account()->status()->unwrap();
echo "{$status->requests->current}/{$status->requests->limitDay} requests used today";
```

Free — does not count against your daily quota.
