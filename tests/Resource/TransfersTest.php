<?php

declare(strict_types=1);

namespace ApiFootball\Tests\Resource;

use ApiFootball\Exception\TransportException;
use ApiFootball\Tests\ResourceTestCase;

final class TransfersTest extends ResourceTestCase
{
    /** Lifted verbatim from the live OpenAPI spec's own `/transfers` example, trimmed to two moves. */
    private const string TRANSFERS_RESPONSE_JSON = <<<'JSON'
        {
            "get": "transfers",
            "parameters": {"player": "35845"},
            "errors": [],
            "results": 1,
            "response": [
                {
                    "player": {"id": 35845, "name": "Hernán Darío Burbano"},
                    "update": "2020-02-06T00:08:15+00:00",
                    "transfers": [
                        {
                            "date": "2019-07-15", "type": "Free",
                            "teams": {
                                "in": {"id": 2283, "name": "Atlas", "logo": "https://media.api-sports.io/football/teams/2283.png"},
                                "out": {"id": 2283, "name": "Atlas", "logo": "https://media.api-sports.io/football/teams/2283.png"}
                            }
                        },
                        {
                            "date": "2019-01-01", "type": "N/A",
                            "teams": {
                                "in": {"id": 1937, "name": "Atletico Atlas", "logo": "https://media.api-sports.io/football/teams/1937.png"},
                                "out": {"id": 1139, "name": "Santa Fe", "logo": "https://media.api-sports.io/football/teams/1139.png"}
                            }
                        }
                    ]
                }
            ]
        }
        JSON;

    public function testListReturnsMappedTransfersOnSuccess(): void
    {
        $client = $this->clientWithResponse(self::TRANSFERS_RESPONSE_JSON);

        $result = $client->transfers()->list(player: 35845);

        self::assertTrue($result->isOk());
        $transfers = $result->unwrap();
        self::assertCount(1, $transfers);

        $transfer = $transfers[0];
        self::assertSame('Hernán Darío Burbano', $transfer->player->name);
        self::assertCount(2, $transfer->transfers);
        self::assertSame('Free', $transfer->transfers[0]->type);
        self::assertSame('Atlas', $transfer->transfers[0]->teamIn->name);
        self::assertSame('Santa Fe', $transfer->transfers[1]->teamOut->name);
    }

    public function testListReturnsErrResultWhenApiReturnsErrorsOnHttp200(): void
    {
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'transfers',
            'errors' => ['player' => 'The player field must be an integer.'],
        ]));

        $result = $client->transfers()->list(player: 0);

        self::assertFalse($result->isOk());
    }

    public function testListThrowsTransportExceptionOnHttpClientFailure(): void
    {
        $client = $this->clientWithTransportFailure();

        $this->expectException(TransportException::class);

        $client->transfers()->list();
    }
}
