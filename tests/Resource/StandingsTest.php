<?php

declare(strict_types=1);

namespace ApiFootball\Tests\Resource;

use ApiFootball\Exception\TransportException;
use ApiFootball\Tests\ResourceTestCase;

final class StandingsTest extends ResourceTestCase
{
    /** Lifted verbatim from the live OpenAPI spec's own `/standings` example. */
    private const string STANDINGS_RESPONSE_JSON = <<<'JSON'
        {
            "get": "standings",
            "parameters": {"league": "39", "season": "2019"},
            "errors": [],
            "results": 1,
            "response": [
                {
                    "league": {
                        "id": 39, "name": "Premier League", "country": "England",
                        "logo": "https://media.api-sports.io/football/leagues/2.png",
                        "flag": "https://media.api-sports.io/flags/gb.svg", "season": 2019,
                        "standings": [
                            [
                                {
                                    "rank": 1,
                                    "team": {"id": 40, "name": "Liverpool", "logo": "https://media.api-sports.io/football/teams/40.png"},
                                    "points": 70, "goalsDiff": 41, "group": "Premier League",
                                    "form": "WWWWW", "status": "same",
                                    "description": "Promotion - Champions League (Group Stage)",
                                    "all": {"played": 24, "win": 23, "draw": 1, "lose": 0, "goals": {"for": 56, "against": 15}},
                                    "home": {"played": 12, "win": 12, "draw": 0, "lose": 0, "goals": {"for": 31, "against": 9}},
                                    "away": {"played": 12, "win": 11, "draw": 1, "lose": 0, "goals": {"for": 25, "against": 6}},
                                    "update": "2020-01-29T00:00:00+00:00"
                                }
                            ]
                        ]
                    }
                }
            ]
        }
        JSON;

    public function testListReturnsMappedStandingsOnSuccess(): void
    {
        $client = $this->clientWithResponse(self::STANDINGS_RESPONSE_JSON);

        $result = $client->standings()->list(season: 2019, league: 39);

        self::assertTrue($result->isOk());
        $tables = $result->unwrap();
        self::assertCount(1, $tables);

        $table = $tables[0];
        self::assertSame('Premier League', $table->league->name);
        self::assertCount(1, $table->standings);
        self::assertCount(1, $table->standings[0]);

        $row = $table->standings[0][0];
        self::assertSame(1, $row->rank);
        self::assertSame('Liverpool', $row->team->name);
        self::assertSame(70, $row->points);
        self::assertSame(41, $row->goalsDiff);
        self::assertSame('Promotion - Champions League (Group Stage)', $row->description);
        self::assertSame(24, $row->all->played);
        self::assertSame(56, $row->all->goals->for);
        self::assertSame(31, $row->home->goals->for);
    }

    public function testListReturnsErrResultWhenApiReturnsErrorsOnHttp200(): void
    {
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'standings',
            'errors' => ['season' => 'The season field is required.'],
        ]));

        $result = $client->standings()->list(season: 0);

        self::assertFalse($result->isOk());
    }

    public function testListThrowsTransportExceptionOnHttpClientFailure(): void
    {
        $client = $this->clientWithTransportFailure();

        $this->expectException(TransportException::class);

        $client->standings()->list(season: 2019);
    }
}
