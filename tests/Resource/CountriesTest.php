<?php

declare(strict_types=1);

namespace ApiFootball\Tests\Resource;

use ApiFootball\Exception\TransportException;
use ApiFootball\Tests\ResourceTestCase;

/**
 * Reference pattern for every resource test: success, API-level-errors-on-HTTP-200 (Result::err, no
 * exception), and transport failure (TransportException). See docs/design/sdk-design.md §4.4.
 */
final class CountriesTest extends ResourceTestCase
{
    public function testListReturnsMappedCountriesOnSuccess(): void
    {
        // Hand-authored to match the documented {name, code, flag} shape — the spec's own `examples`
        // block for /countries wasn't captured verbatim during the schema scan, so unlike a fixture
        // lifted straight from the spec, this one is written by hand.
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'countries',
            'parameters' => ['name' => 'england'],
            'results' => 1,
            'response' => [
                ['name' => 'England', 'code' => 'GB', 'flag' => 'https://media.api-sports.io/flags/gb.svg'],
            ],
        ]));

        $result = $client->countries()->list(name: 'england');

        self::assertTrue($result->isOk());

        $countries = $result->unwrap();
        self::assertCount(1, $countries);
        self::assertSame('England', $countries[0]->name);
        self::assertSame('GB', $countries[0]->code);
        self::assertSame('https://media.api-sports.io/flags/gb.svg', $countries[0]->flag);
    }

    public function testRateLimitIsExposedAfterASuccessfulCall(): void
    {
        $client = $this->clientWithResponse(
            $this->envelopeJson(['get' => 'countries']),
            headers: [
                'x-ratelimit-requests-limit' => '100',
                'x-ratelimit-requests-remaining' => '99',
                'X-RateLimit-Limit' => '30',
                'X-RateLimit-Remaining' => '29',
            ],
        );

        $client->countries()->list();

        $rateLimit = $client->rateLimit();
        self::assertNotNull($rateLimit);
        self::assertSame(100, $rateLimit->dailyLimit);
        self::assertSame(99, $rateLimit->dailyRemaining);
        self::assertSame(30, $rateLimit->perMinuteLimit);
        self::assertSame(29, $rateLimit->perMinuteRemaining);
    }

    public function testListReturnsErrResultWhenApiReturnsErrorsOnHttp200(): void
    {
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'countries',
            'parameters' => ['code' => 'GBR'],
            'errors' => ['code' => 'The code field must be 2 characters.'],
        ]));

        $result = $client->countries()->list(code: 'GBR');

        self::assertFalse($result->isOk());
        self::assertSame(['code' => 'The code field must be 2 characters.'], $result->errors());
    }

    public function testListThrowsTransportExceptionOnHttpClientFailure(): void
    {
        $client = $this->clientWithTransportFailure();

        $this->expectException(TransportException::class);

        $client->countries()->list();
    }
}
