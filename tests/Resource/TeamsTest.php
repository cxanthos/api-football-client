<?php

declare(strict_types=1);

namespace ApiFootball\Tests\Resource;

use ApiFootball\Exception\TransportException;
use ApiFootball\Tests\ResourceTestCase;

final class TeamsTest extends ResourceTestCase
{
    /**
     * Lifted from the live OpenAPI spec's own `/teams/statistics` example (docs/design/sdk-design.md) —
     * real, API-Sports-authored payload, not hand-faked. Trimmed to a representative slice of the
     * minute/threshold buckets rather than all 8/5 entries, since the mapping logic is identical either way.
     */
    private const string TEAM_STATISTICS_RESPONSE_JSON = <<<'JSON'
        {
            "get": "teams/statistics",
            "parameters": {"league": "39", "season": "2019", "team": "33"},
            "errors": [],
            "results": 1,
            "response": {
                "league": {
                    "id": 39, "name": "Premier League", "country": "England",
                    "logo": "https://media.api-sports.io/football/leagues/39.png",
                    "flag": "https://media.api-sports.io/flags/gb-eng.svg", "season": 2019
                },
                "team": {"id": 33, "name": "Manchester United", "logo": "https://media.api-sports.io/football/teams/33.png"},
                "form": "WDLDWLDLDWLWDDWWDLWWLWLLDWWDWDWWWWDWDW",
                "fixtures": {
                    "played": {"home": 19, "away": 19, "total": 38},
                    "wins": {"home": 10, "away": 8, "total": 18},
                    "draws": {"home": 7, "away": 5, "total": 12},
                    "loses": {"home": 2, "away": 6, "total": 8}
                },
                "goals": {
                    "for": {
                        "total": {"home": 40, "away": 26, "total": 66},
                        "average": {"home": "2.1", "away": "1.4", "total": "1.7"},
                        "minute": {"0-15": {"total": 4, "percentage": "6.06%"}},
                        "under_over": {"0.5": {"over": 30, "under": 8}}
                    },
                    "against": {
                        "total": {"home": 17, "away": 19, "total": 36},
                        "average": {"home": "0.9", "away": "1.0", "total": "0.9"},
                        "minute": {"0-15": {"total": 6, "percentage": "16.67%"}},
                        "under_over": {"0.5": {"over": 25, "under": 13}}
                    }
                },
                "biggest": {
                    "streak": {"wins": 4, "draws": 2, "loses": 2},
                    "wins": {"home": "4-0", "away": "0-3"},
                    "loses": {"home": "0-2", "away": "2-0"},
                    "goals": {"for": {"home": 5, "away": 3}, "against": {"home": 2, "away": 3}}
                },
                "clean_sheet": {"home": 7, "away": 6, "total": 13},
                "failed_to_score": {"home": 2, "away": 6, "total": 8},
                "penalty": {
                    "scored": {"total": 10, "percentage": "100.00%"},
                    "missed": {"total": 0, "percentage": "0%"},
                    "total": 10
                },
                "lineups": [
                    {"formation": "4-2-3-1", "played": 32},
                    {"formation": "3-4-1-2", "played": 4}
                ],
                "cards": {
                    "yellow": {"0-15": {"total": 5, "percentage": "6.85%"}},
                    "red": {"0-15": {"total": null, "percentage": null}}
                }
            }
        }
        JSON;

    public function testListReturnsMappedTeamsOnSuccess(): void
    {
        // Hand-authored to match the documented schema shape — no literal /teams example was captured
        // during the schema scan (only the schema itself, not a worked example).
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'teams',
            'results' => 1,
            'response' => [
                [
                    'team' => [
                        'id' => 33, 'name' => 'Manchester United', 'code' => 'MUN', 'country' => 'England',
                        'founded' => 1878, 'national' => false,
                        'logo' => 'https://media.api-sports.io/football/teams/33.png',
                    ],
                    'venue' => [
                        'id' => 556, 'name' => 'Old Trafford', 'address' => 'Sir Matt Busby Way',
                        'city' => 'Manchester', 'capacity' => 76000, 'surface' => 'grass',
                        'image' => 'https://media.api-sports.io/football/venues/556.png',
                    ],
                ],
            ],
        ]));

        $result = $client->teams()->list(id: 33);

        self::assertTrue($result->isOk());
        $teams = $result->unwrap();
        self::assertCount(1, $teams);
        self::assertSame('Manchester United', $teams[0]->name);
        self::assertSame(1878, $teams[0]->founded);
        self::assertSame('Old Trafford', $teams[0]->venue->name);
        self::assertSame(76000, $teams[0]->venue->capacity);
    }

    public function testStatisticsReturnsFullyMappedTreeOnSuccess(): void
    {
        $client = $this->clientWithResponse(self::TEAM_STATISTICS_RESPONSE_JSON);

        $result = $client->teams()->statistics(league: 39, season: 2019, team: 33);

        self::assertTrue($result->isOk());
        $stats = $result->unwrap();

        self::assertSame('Manchester United', $stats->team->name);
        self::assertSame('Premier League', $stats->league->name);
        self::assertSame(38, $stats->fixtures->played->total);
        self::assertSame(66, $stats->goals->for->total->total);
        self::assertSame('1.7', $stats->goals->for->average->total);
        self::assertSame(4, $stats->goals->for->minute['0-15']->total);
        self::assertSame(30, $stats->goals->for->underOver['0.5']->over);
        self::assertSame('4-0', $stats->biggest->wins->home);
        self::assertSame(5, $stats->biggest->goalsFor->home);
        self::assertSame(13, $stats->cleanSheet->total);
        self::assertSame(10, $stats->penalty->total);
        self::assertCount(2, $stats->lineups);
        self::assertSame('4-2-3-1', $stats->lineups[0]->formation);
        self::assertSame(5, $stats->cards->yellow['0-15']->total);
        self::assertNull($stats->cards->red['0-15']->total);
    }

    public function testSeasonsReturnsFlatYearList(): void
    {
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'teams/seasons',
            'results' => 2,
            'response' => [2010, 2011],
        ]));

        $result = $client->teams()->seasons(team: 33);

        self::assertTrue($result->isOk());
        self::assertSame([2010, 2011], $result->unwrap());
    }

    public function testCountriesReturnsMappedCountriesOnSuccess(): void
    {
        // Same {name, code, flag} shape as /countries itself — lifted verbatim from the live spec example.
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'teams/countries',
            'results' => 1,
            'response' => [
                ['name' => 'England', 'code' => 'GB', 'flag' => 'https://media.api-sports.io/flags/gb.svg'],
            ],
        ]));

        $result = $client->teams()->countries();

        self::assertTrue($result->isOk());
        self::assertSame('England', $result->unwrap()[0]->name);
    }

    public function testListReturnsErrResultWhenApiReturnsErrorsOnHttp200(): void
    {
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'teams',
            'errors' => ['league' => 'The league field is required.'],
        ]));

        $result = $client->teams()->list();

        self::assertFalse($result->isOk());
    }

    public function testListThrowsTransportExceptionOnHttpClientFailure(): void
    {
        $client = $this->clientWithTransportFailure();

        $this->expectException(TransportException::class);

        $client->teams()->list();
    }
}
