<?php

declare(strict_types=1);

namespace ApiFootball\Tests\Resource;

use ApiFootball\Exception\TransportException;
use ApiFootball\Tests\ResourceTestCase;

final class CoachsTest extends ResourceTestCase
{
    /** Lifted verbatim from the live OpenAPI spec's own `/coachs` example (Thomas Tuchel), trimmed to two career entries. */
    private const string COACH_RESPONSE_JSON = <<<'JSON'
        {
            "get": "coachs",
            "parameters": {"id": "40"},
            "errors": [],
            "results": 1,
            "response": [
                {
                    "id": 40, "name": "T. Tuchel", "firstname": "Thomas", "lastname": "Tuchel", "age": 47,
                    "birth": {"date": "1973-08-29", "place": "Krumbach", "country": "Germany"},
                    "nationality": "Germany", "height": "192 cm", "weight": "85 kg",
                    "photo": "https://media.api-sports.io/football/coachs/40.png",
                    "team": {"id": 85, "name": "PSG", "logo": "https://media.api-sports.io/football/teams/85.png"},
                    "career": [
                        {"team": {"id": 85, "name": "PSG", "logo": "https://media.api-sports.io/football/teams/85.png"}, "start": "2018-07-01", "end": null},
                        {"team": {"id": 165, "name": "Borussia Dortmund", "logo": "https://media.api-sports.io/football/teams/165.png"}, "start": "2015-07-01", "end": "2017-05-01"}
                    ]
                }
            ]
        }
        JSON;

    public function testListReturnsMappedCoachesOnSuccess(): void
    {
        $client = $this->clientWithResponse(self::COACH_RESPONSE_JSON);

        $result = $client->coachs()->list(id: 40);

        self::assertTrue($result->isOk());
        $coaches = $result->unwrap();
        self::assertCount(1, $coaches);

        $coach = $coaches[0];
        self::assertSame('T. Tuchel', $coach->name);
        self::assertSame('Germany', $coach->birth->country);
        self::assertSame('PSG', $coach->team?->name);
        self::assertCount(2, $coach->career);
        self::assertSame('Borussia Dortmund', $coach->career[1]->team->name);
        self::assertNull($coach->career[0]->end);
        self::assertSame('2017-05-01', $coach->career[1]->end);
    }

    public function testListReturnsErrResultWhenApiReturnsErrorsOnHttp200(): void
    {
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'coachs',
            'errors' => ['search' => 'The search field must be at least 3 characters.'],
        ]));

        $result = $client->coachs()->list(search: 'x');

        self::assertFalse($result->isOk());
    }

    public function testListThrowsTransportExceptionOnHttpClientFailure(): void
    {
        $client = $this->clientWithTransportFailure();

        $this->expectException(TransportException::class);

        $client->coachs()->list();
    }
}
