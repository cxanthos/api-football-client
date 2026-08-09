<?php

declare(strict_types=1);

namespace ApiFootball\Tests\Resource;

use ApiFootball\Exception\TransportException;
use ApiFootball\Tests\ResourceTestCase;
use InvalidArgumentException;

final class PlayersTest extends ResourceTestCase
{
    /** Lifted verbatim from the live OpenAPI spec's own `/players` example (Neymar). */
    private const string PLAYER_STATISTICS_RESPONSE_JSON = <<<'JSON'
        {
            "get": "players",
            "parameters": {"id": "276", "season": "2019"},
            "errors": [],
            "results": 1,
            "paging": {"current": 1, "total": 1},
            "response": [
                {
                    "player": {
                        "id": 276, "name": "Neymar", "firstname": "Neymar", "lastname": "da Silva Santos Júnior",
                        "age": 28, "birth": {"date": "1992-02-05", "place": "Mogi das Cruzes", "country": "Brazil"},
                        "nationality": "Brazil", "height": "175 cm", "weight": "68 kg", "injured": false,
                        "photo": "https://media.api-sports.io/football/players/276.png"
                    },
                    "statistics": [
                        {
                            "team": {"id": 85, "name": "Paris Saint Germain", "logo": "https://media.api-sports.io/football/teams/85.png"},
                            "league": {"id": 61, "name": "Ligue 1", "country": "France", "logo": "https://media.api-sports.io/football/leagues/61.png", "flag": "https://media.api-sports.io/flags/fr.svg", "season": 2019},
                            "games": {"appearences": 15, "lineups": 15, "minutes": 1322, "number": null, "position": "Attacker", "rating": "8.053333", "captain": false},
                            "substitutes": {"in": 0, "out": 3, "bench": 0},
                            "shots": {"total": 70, "on": 36},
                            "goals": {"total": 13, "conceded": null, "assists": 6, "saves": 0},
                            "passes": {"total": 704, "key": 39, "accuracy": 79},
                            "tackles": {"total": 13, "blocks": 0, "interceptions": 4},
                            "duels": {"total": null, "won": null},
                            "dribbles": {"attempts": 143, "success": 88, "past": null},
                            "fouls": {"drawn": 62, "committed": 14},
                            "cards": {"yellow": 3, "yellowred": 1, "red": 0},
                            "penalty": {"won": 1, "commited": null, "scored": 4, "missed": 1, "saved": null}
                        }
                    ]
                }
            ]
        }
        JSON;

    /** Lifted verbatim from the live OpenAPI spec's own `/players/squads` example, trimmed to one player. */
    private const string SQUADS_RESPONSE_JSON = <<<'JSON'
        {
            "get": "players/squads",
            "parameters": {"team": "33"},
            "errors": [],
            "results": 1,
            "response": [
                {
                    "team": {"id": 33, "name": "Manchester United", "logo": "https://media.api-sports.io/football/teams/33.png"},
                    "players": [
                        {"id": 882, "name": "David de Gea", "age": 31, "number": 1, "position": "Goalkeeper", "photo": "https://media.api-sports.io/football/players/882.png"}
                    ]
                }
            ]
        }
        JSON;

    public function testStatisticsReturnsFullyMappedTreeOnSuccess(): void
    {
        $client = $this->clientWithResponse(self::PLAYER_STATISTICS_RESPONSE_JSON);

        $result = $client->players()->statistics(id: 276, season: 2019);

        self::assertTrue($result->isOk());
        $entries = $result->unwrap();
        self::assertCount(1, $entries);

        $entry = $entries[0];
        self::assertSame('Neymar', $entry->player->name);
        self::assertSame('Brazil', $entry->player->birth->country);
        self::assertFalse($entry->player->injured);
        self::assertCount(1, $entry->statistics);

        $season = $entry->statistics[0];
        self::assertSame('Paris Saint Germain', $season->team->name);
        self::assertSame('Ligue 1', $season->league->name);
        self::assertSame('8.053333', $season->games->rating);
        self::assertSame(13, $season->goals->total);
        self::assertNull($season->goals->conceded);
        self::assertSame(79, $season->passes->accuracy);
        self::assertSame(1, $season->penalty->won);
        self::assertNull($season->penalty->commited);
    }

    public function testSquadsReturnsMappedRosterOnSuccess(): void
    {
        $client = $this->clientWithResponse(self::SQUADS_RESPONSE_JSON);

        $result = $client->players()->squads(team: 33);

        self::assertTrue($result->isOk());
        $squads = $result->unwrap();
        self::assertCount(1, $squads);
        self::assertSame('Manchester United', $squads[0]->team->name);
        self::assertSame('David de Gea', $squads[0]->players[0]->name);
        self::assertSame('Goalkeeper', $squads[0]->players[0]->position);
    }

    public function testSquadsThrowsWhenNeitherTeamNorPlayerGiven(): void
    {
        $client = $this->clientWithResponse($this->envelopeJson(['get' => 'players/squads']));

        $this->expectException(InvalidArgumentException::class);

        $client->players()->squads();
    }

    public function testTopScorersReturnsMappedEntriesOnSuccess(): void
    {
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'players/topscorers',
            'results' => 1,
            'response' => [
                [
                    'player' => ['id' => 278, 'name' => 'K. Mbappé', 'injured' => false],
                    'statistics' => [
                        ['team' => ['id' => 85, 'name' => 'Paris Saint Germain'], 'league' => ['id' => 61, 'name' => 'Ligue 1'], 'goals' => ['total' => 33]],
                    ],
                ],
            ],
        ]));

        $result = $client->players()->topScorers(league: 61, season: 2018);

        self::assertTrue($result->isOk());
        self::assertSame('K. Mbappé', $result->unwrap()[0]->player->name);
        self::assertSame(33, $result->unwrap()[0]->statistics[0]->goals->total);
    }

    public function testTopAssistsReturnsMappedEntriesOnSuccess(): void
    {
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'players/topassists',
            'results' => 1,
            'response' => [
                ['player' => ['id' => 667, 'name' => 'M. Depay', 'injured' => false], 'statistics' => [['goals' => ['assists' => 9]]]],
            ],
        ]));

        $result = $client->players()->topAssists(league: 61, season: 2020);

        self::assertSame(9, $result->unwrap()[0]->statistics[0]->goals->assists);
    }

    public function testTopYellowCardsAndTopRedCardsHitTheirOwnEndpoints(): void
    {
        $yellowClient = $this->clientWithResponse($this->envelopeJson(['get' => 'players/topyellowcards']));
        $redClient = $this->clientWithResponse($this->envelopeJson(['get' => 'players/topredcards']));

        self::assertTrue($yellowClient->players()->topYellowCards(league: 39, season: 2023)->isOk());
        self::assertTrue($redClient->players()->topRedCards(league: 39, season: 2023)->isOk());
    }

    public function testStatisticsReturnsErrResultWhenApiReturnsErrorsOnHttp200(): void
    {
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'players',
            'errors' => ['page' => 'The page field must be an integer.'],
        ]));

        $result = $client->players()->statistics(id: 276);

        self::assertFalse($result->isOk());
    }

    public function testStatisticsThrowsTransportExceptionOnHttpClientFailure(): void
    {
        $client = $this->clientWithTransportFailure();

        $this->expectException(TransportException::class);

        $client->players()->statistics(id: 276);
    }

    public function testProfilesReturnsTheDistinctProfileShapeOnSuccess(): void
    {
        // Lifted verbatim from the live OpenAPI spec's own `/players/profiles` example (Neymar) — note
        // this shape has number/position but no injured, unlike statistics()'s player block.
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'players/profiles',
            'results' => 1,
            'response' => [
                [
                    'player' => [
                        'id' => 276, 'name' => 'Neymar', 'firstname' => 'Neymar', 'lastname' => 'da Silva Santos Júnior',
                        'age' => 32, 'birth' => ['date' => '1992-02-05', 'place' => 'Mogi das Cruzes', 'country' => 'Brazil'],
                        'nationality' => 'Brazil', 'height' => '175 cm', 'weight' => '68 kg',
                        'number' => 10, 'position' => 'Attacker',
                        'photo' => 'https://media.api-sports.io/football/players/276.png',
                    ],
                ],
            ],
        ]));

        $result = $client->players()->profiles(player: 276);

        self::assertTrue($result->isOk());
        $profile = $result->unwrap()[0];
        self::assertSame('Neymar', $profile->name);
        self::assertSame(10, $profile->number);
        self::assertSame('Attacker', $profile->position);
        self::assertSame('Brazil', $profile->birth->country);
    }

    public function testSeasonsReturnsFlatYearList(): void
    {
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'players/seasons',
            'results' => 2,
            'response' => [1966, 2020],
        ]));

        $result = $client->players()->seasons(player: 276);

        self::assertTrue($result->isOk());
        self::assertSame([1966, 2020], $result->unwrap());
    }

    public function testTeamsReturnsCareerPathOnSuccess(): void
    {
        // Lifted verbatim from the live OpenAPI spec's own `/players/teams` example (Brazil NT), trimmed
        // to two seasons.
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'players/teams',
            'results' => 1,
            'response' => [
                [
                    'team' => ['id' => 6, 'name' => 'Brazil', 'logo' => 'https://media.api-sports.io/football/teams/6.png'],
                    'seasons' => [2023, 2022],
                ],
            ],
        ]));

        $result = $client->players()->teams(player: 276);

        self::assertTrue($result->isOk());
        $entry = $result->unwrap()[0];
        self::assertSame('Brazil', $entry->team->name);
        self::assertSame([2023, 2022], $entry->seasons);
    }
}
