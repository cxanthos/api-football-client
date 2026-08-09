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

    public function testRoundsReturnsFlatRoundNameList(): void
    {
        // Lifted verbatim from the live OpenAPI spec's own `/fixtures/rounds` example.
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'fixtures/rounds',
            'results' => 1,
            'response' => ['Regular Season - 1'],
        ]));

        $result = $client->fixtures()->rounds(league: 39, season: 2023);

        self::assertTrue($result->isOk());
        self::assertSame(['Regular Season - 1'], $result->unwrap());
    }

    public function testLineupsReturnsFullyMappedTreeOnSuccess(): void
    {
        // Lifted verbatim from the live OpenAPI spec's own `/fixtures/lineups` example (Manchester City),
        // trimmed to two starters and two substitutes.
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'fixtures/lineups',
            'results' => 1,
            'response' => [
                [
                    'team' => [
                        'id' => 50, 'name' => 'Manchester City', 'logo' => 'https://media.api-sports.io/football/teams/50.png',
                        'colors' => [
                            'player' => ['primary' => '5badff', 'number' => 'ffffff', 'border' => '99ff99'],
                            'goalkeeper' => ['primary' => '99ff99', 'number' => '000000', 'border' => '99ff99'],
                        ],
                    ],
                    'formation' => '4-3-3',
                    'startXI' => [
                        ['player' => ['id' => 617, 'name' => 'Ederson', 'number' => 31, 'pos' => 'G', 'grid' => '1:1']],
                        ['player' => ['id' => 627, 'name' => 'Kyle Walker', 'number' => 2, 'pos' => 'D', 'grid' => '2:4']],
                    ],
                    'substitutes' => [
                        ['player' => ['id' => 50828, 'name' => 'Zack Steffen', 'number' => 13, 'pos' => 'G', 'grid' => null]],
                        ['player' => ['id' => 623, 'name' => 'Benjamin Mendy', 'number' => 22, 'pos' => 'D', 'grid' => null]],
                    ],
                    'coach' => ['id' => 4, 'name' => 'Guardiola', 'photo' => 'https://media.api-sports.io/football/coachs/4.png'],
                ],
            ],
        ]));

        $result = $client->fixtures()->lineups(fixture: 592872);

        self::assertTrue($result->isOk());
        $lineup = $result->unwrap()[0];
        self::assertSame('Manchester City', $lineup->team->name);
        self::assertSame('5badff', $lineup->team->colors->player->primary);
        self::assertSame('4-3-3', $lineup->formation);
        self::assertCount(2, $lineup->startXI);
        self::assertSame('Ederson', $lineup->startXI[0]->player->name);
        self::assertSame('1:1', $lineup->startXI[0]->player->grid);
        self::assertNull($lineup->substitutes[0]->player->grid);
        self::assertSame('Guardiola', $lineup->coach->name);
    }

    public function testStatisticsReturnsMappedStatItemsOnSuccess(): void
    {
        // Lifted verbatim from the live OpenAPI spec's own `/fixtures/statistics` example (Aldosivi),
        // trimmed to three stat lines covering all three value shapes (int, percentage string, null).
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'fixtures/statistics',
            'results' => 1,
            'response' => [
                [
                    'team' => ['id' => 463, 'name' => 'Aldosivi', 'logo' => 'https://media.api-sports.io/football/teams/463.png'],
                    'statistics' => [
                        ['type' => 'Total Shots', 'value' => 9],
                        ['type' => 'Ball Possession', 'value' => '32%'],
                        ['type' => 'Goalkeeper Saves', 'value' => null],
                    ],
                ],
            ],
        ]));

        $result = $client->fixtures()->statistics(fixture: 157201);

        self::assertTrue($result->isOk());
        $teamStats = $result->unwrap()[0];
        self::assertSame('Aldosivi', $teamStats->team->name);
        self::assertSame(9, $teamStats->statistics[0]->value);
        self::assertSame('32%', $teamStats->statistics[1]->value);
        self::assertNull($teamStats->statistics[2]->value);
    }

    public function testPlayersReturnsFullyMappedTreeOnSuccess(): void
    {
        // Lifted verbatim from the live OpenAPI spec's own `/fixtures/players` example (Monarcas).
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'fixtures/players',
            'results' => 1,
            'response' => [
                [
                    'team' => ['id' => 2284, 'name' => 'Monarcas', 'logo' => 'https://media.api-sports.io/football/teams/2284.png', 'update' => '2020-01-13T16:12:12+00:00'],
                    'players' => [
                        [
                            'player' => ['id' => 35931, 'name' => 'Sebastián Sosa', 'photo' => 'https://media.api-sports.io/football/players/35931.png'],
                            'statistics' => [
                                [
                                    'games' => ['minutes' => 90, 'number' => 13, 'position' => 'G', 'rating' => '6.3', 'captain' => false, 'substitute' => false],
                                    'offsides' => null,
                                    'shots' => ['total' => 0, 'on' => 0],
                                    'goals' => ['total' => null, 'conceded' => 1, 'assists' => null, 'saves' => 0],
                                    'passes' => ['total' => 17, 'key' => 0, 'accuracy' => '68%'],
                                    'tackles' => ['total' => null, 'blocks' => 0, 'interceptions' => 0],
                                    'duels' => ['total' => null, 'won' => null],
                                    'dribbles' => ['attempts' => 0, 'success' => 0, 'past' => null],
                                    'fouls' => ['drawn' => 0, 'committed' => 0],
                                    'cards' => ['yellow' => 0, 'red' => 0],
                                    'penalty' => ['won' => null, 'commited' => null, 'scored' => 0, 'missed' => 0, 'saved' => 0],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]));

        $result = $client->fixtures()->players(fixture: 592872);

        self::assertTrue($result->isOk());
        $teamPlayers = $result->unwrap()[0];
        self::assertSame('Monarcas', $teamPlayers->team->name);
        self::assertSame('2020-01-13T16:12:12+00:00', $teamPlayers->updatedAt);

        $entry = $teamPlayers->players[0];
        self::assertSame('Sebastián Sosa', $entry->player->name);
        $stats = $entry->statistics[0];
        self::assertSame(90, $stats->games->minutes);
        self::assertFalse($stats->games->substitute);
        self::assertSame('68%', $stats->passes->accuracy);
        self::assertSame(1, $stats->goals->conceded);
        self::assertSame(0, $stats->cards->yellow);
    }
}
