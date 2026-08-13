<?php

declare(strict_types=1);

namespace ApiFootball\Tests\Resource;

use ApiFootball\ErrorId;
use ApiFootball\Exception\TransportException;
use ApiFootball\Tests\ResourceTestCase;

final class LeaguesTest extends ResourceTestCase
{
    /**
     * Lifted from the live OpenAPI spec's own `/leagues` example (docs/design/sdk-design.md) — real,
     * API-Sports-authored payload, not hand-faked.
     */
    private const string LEAGUES_RESPONSE_JSON = <<<'JSON'
        {
            "get": "leagues",
            "parameters": {"id": "39"},
            "errors": [],
            "results": 1,
            "paging": {"current": 1, "total": 1},
            "response": [
                {
                    "league": {
                        "id": 39,
                        "name": "Premier League",
                        "type": "League",
                        "logo": "https://media.api-sports.io/football/leagues/2.png"
                    },
                    "country": {
                        "name": "England",
                        "code": "GB",
                        "flag": "https://media.api-sports.io/flags/gb.svg"
                    },
                    "seasons": [
                        {
                            "year": 2019,
                            "start": "2019-08-09",
                            "end": "2020-07-26",
                            "current": false,
                            "coverage": {
                                "fixtures": {
                                    "events": true,
                                    "lineups": true,
                                    "statistics_fixtures": false,
                                    "statistics_players": false
                                },
                                "standings": true,
                                "players": true,
                                "top_scorers": true,
                                "top_assists": true,
                                "top_cards": true,
                                "injuries": true,
                                "predictions": true,
                                "odds": false
                            }
                        }
                    ]
                }
            ]
        }
        JSON;

    public function testListReturnsMappedLeaguesOnSuccess(): void
    {
        $client = $this->clientWithResponse(self::LEAGUES_RESPONSE_JSON);

        $result = $client->leagues()->list(id: 39);

        self::assertTrue($result->isOk());
        $leagues = $result->unwrap();
        self::assertCount(1, $leagues);

        $league = $leagues[0];
        self::assertSame(39, $league->id);
        self::assertSame('Premier League', $league->name);
        self::assertSame('England', $league->country->name);
        self::assertSame('GB', $league->country->code);
        self::assertCount(1, $league->seasons);
        self::assertSame(2019, $league->seasons[0]->year);
        self::assertTrue($league->seasons[0]->coverage->fixtureEvents);
        self::assertTrue($league->seasons[0]->coverage->topAssists);
        self::assertFalse($league->seasons[0]->coverage->odds);
    }

    public function testCoverageReturnsMatchingSeasonsCoverage(): void
    {
        $client = $this->clientWithResponse(self::LEAGUES_RESPONSE_JSON);

        $result = $client->leagues()->coverage(id: 39, season: 2019);

        self::assertTrue($result->isOk());
        $coverage = $result->unwrap();
        self::assertTrue($coverage->supports('top_assists'));
        self::assertTrue($coverage->supports('top_cards'));
        self::assertFalse($coverage->supports('odds'));
    }

    public function testCoverageReturnsErrWhenSeasonNotPresent(): void
    {
        $client = $this->clientWithResponse(self::LEAGUES_RESPONSE_JSON);

        $result = $client->leagues()->coverage(id: 39, season: 1999);

        self::assertFalse($result->isOk());
    }

    public function testSeasonsReturnsFlatYearList(): void
    {
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'leagues/seasons',
            'results' => 2,
            'response' => [2019, 2020],
        ]));

        $result = $client->leagues()->seasons();

        self::assertTrue($result->isOk());
        self::assertSame([2019, 2020], $result->unwrap());
    }

    public function testListReturnsErrResultWhenApiReturnsErrorsOnHttp200(): void
    {
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'leagues',
            'errors' => ['season' => 'The season field must be 4 characters.'],
        ]));

        $result = $client->leagues()->list(season: 19);

        self::assertFalse($result->isOk());
        self::assertSame(['season' => 'The season field must be 4 characters.'], $result->errors());
    }

    public function testListThrowsTransportExceptionOnHttpClientFailure(): void
    {
        $client = $this->clientWithTransportFailure();

        $this->expectException(TransportException::class);

        $client->leagues()->list();
    }

    public function testListResultCarriesRateLimitedErrorIdOnHttp429(): void
    {
        $client = $this->clientWithResponse(
            $this->envelopeJson([
                'get' => 'leagues',
                'errors' => ['rateLimit' => 'Too many requests'],
            ]),
            status: 429,
        );

        $result = $client->leagues()->list();

        self::assertFalse($result->isOk());
        self::assertSame(ErrorId::RateLimited, $result->errorId());
    }

    public function testListResultHasNoErrorIdForOrdinaryApiLevelErrors(): void
    {
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'leagues',
            'errors' => ['season' => 'The season field must be 4 characters.'],
        ]));

        $result = $client->leagues()->list(season: 19);

        self::assertFalse($result->isOk());
        self::assertNull($result->errorId());
    }
}
