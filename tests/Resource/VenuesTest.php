<?php

declare(strict_types=1);

namespace ApiFootball\Tests\Resource;

use ApiFootball\Exception\TransportException;
use ApiFootball\Tests\ResourceTestCase;

final class VenuesTest extends ResourceTestCase
{
    public function testListReturnsMappedVenuesOnSuccess(): void
    {
        // Hand-authored to match the documented schema shape — no literal /venues example was captured
        // during the schema scan (only the schema itself, not a worked example).
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'venues',
            'results' => 1,
            'response' => [
                [
                    'id' => 556, 'name' => 'Old Trafford', 'address' => 'Sir Matt Busby Way',
                    'city' => 'Manchester', 'country' => 'England', 'capacity' => 76000,
                    'surface' => 'grass', 'image' => 'https://media.api-sports.io/football/venues/556.png',
                ],
            ],
        ]));

        $result = $client->venues()->list(city: 'Manchester');

        self::assertTrue($result->isOk());
        $venues = $result->unwrap();
        self::assertCount(1, $venues);
        self::assertSame('Old Trafford', $venues[0]->name);
        self::assertSame('England', $venues[0]->country);
        self::assertSame(76000, $venues[0]->capacity);
    }

    public function testListReturnsErrResultWhenApiReturnsErrorsOnHttp200(): void
    {
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'venues',
            'errors' => ['search' => 'The search field must be at least 3 characters.'],
        ]));

        $result = $client->venues()->list(search: 'x');

        self::assertFalse($result->isOk());
    }

    public function testListThrowsTransportExceptionOnHttpClientFailure(): void
    {
        $client = $this->clientWithTransportFailure();

        $this->expectException(TransportException::class);

        $client->venues()->list();
    }
}
