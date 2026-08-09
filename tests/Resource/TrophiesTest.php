<?php

declare(strict_types=1);

namespace ApiFootball\Tests\Resource;

use ApiFootball\Exception\TransportException;
use ApiFootball\Tests\ResourceTestCase;

final class TrophiesTest extends ResourceTestCase
{
    public function testListReturnsMappedTrophiesOnSuccess(): void
    {
        // Lifted verbatim from the live OpenAPI spec's own `/trophies` example.
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'trophies',
            'results' => 1,
            'response' => [
                ['league' => 'Sudamericano U20', 'country' => 'South-America', 'season' => 'Peru 2011', 'place' => 'Winner'],
            ],
        ]));

        $result = $client->trophies()->list(player: 1234);

        self::assertTrue($result->isOk());
        $trophies = $result->unwrap();
        self::assertCount(1, $trophies);
        self::assertSame('Sudamericano U20', $trophies[0]->league);
        self::assertSame('Peru 2011', $trophies[0]->season);
        self::assertSame('Winner', $trophies[0]->place);
    }

    public function testListReturnsErrResultWhenApiReturnsErrorsOnHttp200(): void
    {
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'trophies',
            'errors' => ['player' => 'At least one parameter is required.'],
        ]));

        $result = $client->trophies()->list();

        self::assertFalse($result->isOk());
    }

    public function testListThrowsTransportExceptionOnHttpClientFailure(): void
    {
        $client = $this->clientWithTransportFailure();

        $this->expectException(TransportException::class);

        $client->trophies()->list(player: 1234);
    }
}
