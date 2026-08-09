<?php

declare(strict_types=1);

namespace ApiFootball\Tests\Resource;

use ApiFootball\DTO\FixtureStatusCode;
use ApiFootball\Exception\TransportException;
use ApiFootball\Tests\ResourceTestCase;

final class FixturesTest extends ResourceTestCase
{
    /** Lifted verbatim from the live OpenAPI spec's own `/fixtures` example. */
    private const string FIXTURE_RESPONSE_JSON = <<<'JSON'
        {
            "get": "fixtures",
            "parameters": {},
            "errors": [],
            "results": 1,
            "response": [
                {
                    "fixture": {
                        "id": 239625, "referee": null, "timezone": "UTC",
                        "date": "2020-02-06T14:00:00+00:00", "timestamp": 1580997600,
                        "periods": {"first": 1580997600, "second": null},
                        "venue": {"id": 1887, "name": "Stade Municipal", "city": "Oued Zem"},
                        "status": {"long": "Halftime", "short": "HT", "elapsed": 45, "extra": null}
                    },
                    "league": {
                        "id": 200, "name": "Botola Pro", "country": "Morocco",
                        "logo": "https://media.api-sports.io/football/leagues/115.png",
                        "flag": "https://media.api-sports.io/flags/ma.svg", "season": 2019,
                        "round": "Regular Season - 14"
                    },
                    "teams": {
                        "home": {"id": 967, "name": "Rapide Oued ZEM", "logo": "https://media.api-sports.io/football/teams/967.png", "winner": false},
                        "away": {"id": 968, "name": "Wydad AC", "logo": "https://media.api-sports.io/football/teams/968.png", "winner": true}
                    },
                    "goals": {"home": 0, "away": 1},
                    "score": {
                        "halftime": {"home": 0, "away": 1},
                        "fulltime": {"home": null, "away": null},
                        "extratime": {"home": null, "away": null},
                        "penalty": {"home": null, "away": null}
                    }
                }
            ]
        }
        JSON;

    /** Lifted verbatim from the live OpenAPI spec's own `/fixtures/events` example. */
    private const string EVENTS_RESPONSE_JSON = <<<'JSON'
        {
            "get": "fixtures/events",
            "parameters": {"fixture": "169080"},
            "errors": [],
            "results": 1,
            "response": [
                {
                    "time": {"elapsed": 25, "extra": null},
                    "team": {"id": 463, "name": "Aldosivi", "logo": "https://media.api-sports.io/football/teams/463.png"},
                    "player": {"id": 6126, "name": "F. Andrada"},
                    "assist": {"id": null, "name": null},
                    "type": "Goal",
                    "detail": "Normal Goal",
                    "comments": null
                }
            ]
        }
        JSON;

    public function testListReturnsMappedFixturesOnSuccess(): void
    {
        $client = $this->clientWithResponse(self::FIXTURE_RESPONSE_JSON);

        $result = $client->fixtures()->list(league: 200, season: 2019);

        self::assertTrue($result->isOk());
        $fixtures = $result->unwrap();
        self::assertCount(1, $fixtures);

        $fixture = $fixtures[0];
        self::assertSame(239625, $fixture->fixture->id);
        self::assertNull($fixture->fixture->referee);
        self::assertSame(FixtureStatusCode::Halftime, $fixture->fixture->status->code);
        self::assertSame(45, $fixture->fixture->status->elapsed);
        self::assertSame('Botola Pro', $fixture->league->name);
        self::assertSame('Regular Season - 14', $fixture->league->round);
        self::assertSame('Rapide Oued ZEM', $fixture->teams->home->name);
        self::assertFalse($fixture->teams->home->winner);
        self::assertTrue($fixture->teams->away->winner);
        self::assertSame(0, $fixture->goals->home);
        self::assertSame(1, $fixture->goals->away);
        self::assertSame(0, $fixture->score->halftime->home);
        self::assertNull($fixture->score->fulltime->home);
    }

    public function testUnrecognizedStatusCodeDegradesGracefullyInsteadOfThrowing(): void
    {
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'fixtures',
            'results' => 1,
            'response' => [
                [
                    'fixture' => ['id' => 1, 'timezone' => 'UTC', 'date' => '', 'timestamp' => 0, 'status' => ['long' => 'Something New', 'short' => 'XYZ']],
                    'league' => ['id' => 1, 'name' => 'X'],
                    'teams' => ['home' => ['id' => 1, 'name' => 'A'], 'away' => ['id' => 2, 'name' => 'B']],
                    'goals' => ['home' => 0, 'away' => 0],
                    'score' => [],
                ],
            ],
        ]));

        $result = $client->fixtures()->list();

        self::assertTrue($result->isOk());
        $fixture = $result->unwrap()[0];
        self::assertNull($fixture->fixture->status->code);
        self::assertSame('XYZ', $fixture->fixture->status->raw);
    }

    public function testEventsReturnsMappedEventsOnSuccess(): void
    {
        $client = $this->clientWithResponse(self::EVENTS_RESPONSE_JSON);

        $result = $client->fixtures()->events(fixture: 169080);

        self::assertTrue($result->isOk());
        $events = $result->unwrap();
        self::assertCount(1, $events);
        self::assertSame('Goal', $events[0]->type);
        self::assertSame('Normal Goal', $events[0]->detail);
        self::assertSame('F. Andrada', $events[0]->player->name);
        self::assertNull($events[0]->assist->name);
        self::assertSame('Aldosivi', $events[0]->team->name);
    }

    public function testHeadToHeadReturnsMappedFixturesOnSuccess(): void
    {
        $client = $this->clientWithResponse(self::FIXTURE_RESPONSE_JSON);

        $result = $client->fixtures()->headToHead(h2h: '967-968');

        self::assertTrue($result->isOk());
        self::assertCount(1, $result->unwrap());
    }

    public function testListReturnsErrResultWhenApiReturnsErrorsOnHttp200(): void
    {
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'fixtures',
            'errors' => ['date' => 'The date is not a valid date.'],
        ]));

        $result = $client->fixtures()->list(date: 'not-a-date');

        self::assertFalse($result->isOk());
    }

    public function testListThrowsTransportExceptionOnHttpClientFailure(): void
    {
        $client = $this->clientWithTransportFailure();

        $this->expectException(TransportException::class);

        $client->fixtures()->list();
    }
}
