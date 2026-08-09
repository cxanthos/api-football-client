<?php

declare(strict_types=1);

namespace ApiFootball\Tests\Resource;

use ApiFootball\Client;
use ApiFootball\Exception\TransportException;
use Exception;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * Reference pattern for every future resource test: build a Client against a php-http/mock-client double,
 * cover the success path, the API-level-errors-on-HTTP-200 path (Result::err, no exception), and the
 * transport-failure path (TransportException). See docs/design/sdk-design.md §4.4.
 */
final class CountriesTest extends TestCase
{
    public function testListReturnsMappedCountriesOnSuccess(): void
    {
        $mockClient = new MockClient();
        $psr17 = new Psr17Factory();

        // Hand-authored to match the documented {name, code, flag} shape — the spec's own `examples`
        // block for /countries wasn't captured verbatim during the schema scan (see plan/design doc),
        // so unlike a fixture lifted straight from the spec, this one is written by hand.
        $mockClient->addResponse(new Response(
            status: 200,
            headers: [
                'Content-Type' => 'application/json',
                'x-ratelimit-requests-limit' => '100',
                'x-ratelimit-requests-remaining' => '99',
                'X-RateLimit-Limit' => '30',
                'X-RateLimit-Remaining' => '29',
            ],
            body: (string) json_encode([
                'get' => 'countries',
                'parameters' => ['name' => 'england'],
                'errors' => [],
                'results' => 1,
                'response' => [
                    ['name' => 'England', 'code' => 'GB', 'flag' => 'https://media.api-sports.io/flags/gb.svg'],
                ],
            ], JSON_THROW_ON_ERROR),
        ));

        $client = new Client(
            apiKey: 'test-key',
            httpClient: $mockClient,
            requestFactory: $psr17,
            uriFactory: $psr17,
        );

        $result = $client->countries()->list(name: 'england');

        self::assertTrue($result->isOk());

        $countries = $result->unwrap();
        self::assertCount(1, $countries);
        self::assertSame('England', $countries[0]->name);
        self::assertSame('GB', $countries[0]->code);
        self::assertSame('https://media.api-sports.io/flags/gb.svg', $countries[0]->flag);

        $rateLimit = $client->rateLimit();
        self::assertNotNull($rateLimit);
        self::assertSame(100, $rateLimit->dailyLimit);
        self::assertSame(99, $rateLimit->dailyRemaining);
        self::assertSame(30, $rateLimit->perMinuteLimit);
        self::assertSame(29, $rateLimit->perMinuteRemaining);
    }

    public function testListReturnsErrResultWhenApiReturnsErrorsOnHttp200(): void
    {
        $mockClient = new MockClient();
        $psr17 = new Psr17Factory();

        $mockClient->addResponse(new Response(
            status: 200,
            headers: ['Content-Type' => 'application/json'],
            body: (string) json_encode([
                'get' => 'countries',
                'parameters' => ['code' => 'GBR'],
                'errors' => ['code' => 'The code field must be 2 characters.'],
                'results' => 0,
                'response' => [],
            ], JSON_THROW_ON_ERROR),
        ));

        $client = new Client(
            apiKey: 'test-key',
            httpClient: $mockClient,
            requestFactory: $psr17,
            uriFactory: $psr17,
        );

        $result = $client->countries()->list(code: 'GBR');

        self::assertFalse($result->isOk());
        self::assertSame(['code' => 'The code field must be 2 characters.'], $result->errors());
    }

    public function testListThrowsTransportExceptionOnHttpClientFailure(): void
    {
        $mockClient = new MockClient();
        $psr17 = new Psr17Factory();

        $mockClient->addException(new class extends Exception implements ClientExceptionInterface {});

        $client = new Client(
            apiKey: 'test-key',
            httpClient: $mockClient,
            requestFactory: $psr17,
            uriFactory: $psr17,
        );

        $this->expectException(TransportException::class);

        $client->countries()->list();
    }
}
