<?php

declare(strict_types=1);

namespace ApiFootball\Tests\Http;

use ApiFootball\Http\ThrottlingClient;
use Exception;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;

final class ThrottlingClientTest extends TestCase
{
    private const array RATE_LIMIT_HEADERS = [
        'x-ratelimit-requests-limit' => '100',
        'x-ratelimit-requests-remaining' => '90',
        'X-RateLimit-Limit' => '2',
        'X-RateLimit-Remaining' => '1',
    ];

    public function testFirstCallIsNeverThrottledBecauseTheLimitIsUnknownYet(): void
    {
        $mock = new MockClient();
        $mock->addResponse(new Response(200, self::RATE_LIMIT_HEADERS));
        $sleeper = new FakeSleeper();
        $client = new ThrottlingClient($mock, sleeper: $sleeper);

        $client->sendRequest(new Request('GET', 'https://example.test/'));

        self::assertSame([], $sleeper->calls);
    }

    public function testWaitsForTheWindowOnceThePerMinuteLimitIsExceeded(): void
    {
        $mock = new MockClient();
        // Limit of 2/min: the 3rd call within the same 60s window must wait.
        $mock->addResponse(new Response(200, self::RATE_LIMIT_HEADERS));
        $mock->addResponse(new Response(200, self::RATE_LIMIT_HEADERS));
        $mock->addResponse(new Response(200, self::RATE_LIMIT_HEADERS));
        $sleeper = new FakeSleeper();
        $client = new ThrottlingClient($mock, sleeper: $sleeper);

        $client->sendRequest(new Request('GET', 'https://example.test/')); // learns limit=2, no wait
        $client->sendRequest(new Request('GET', 'https://example.test/')); // 2nd within window, no wait yet
        $client->sendRequest(new Request('GET', 'https://example.test/')); // 3rd — must wait ~60s

        self::assertCount(1, $sleeper->calls);
        self::assertGreaterThan(59.0, $sleeper->calls[0]);
        self::assertLessThanOrEqual(60.0, $sleeper->calls[0]);
    }

    public function testAddsCooperativeDelayWhenDailyRemainingIsLow(): void
    {
        $mock = new MockClient();
        $mock->addResponse(new Response(200, [
            'x-ratelimit-requests-limit' => '100',
            'x-ratelimit-requests-remaining' => '5', // below the default threshold of 50
            'X-RateLimit-Limit' => '30',
            'X-RateLimit-Remaining' => '29',
        ]));
        $sleeper = new FakeSleeper();
        $client = new ThrottlingClient($mock, lowDailyRemainingDelaySeconds: 2.5, sleeper: $sleeper);

        $client->sendRequest(new Request('GET', 'https://example.test/'));

        self::assertSame([2.5], $sleeper->calls);
    }

    public function testNeverRetriesA429ItJustPassesTheResponseThrough(): void
    {
        $mock = new MockClient();
        $mock->addResponse(new Response(429, self::RATE_LIMIT_HEADERS));
        $sleeper = new FakeSleeper();
        $client = new ThrottlingClient($mock, sleeper: $sleeper);

        $response = $client->sendRequest(new Request('GET', 'https://example.test/'));

        // Only one response was ever queued; if this had retried, the mock client would have thrown
        // for lack of a second queued response instead of returning normally.
        self::assertSame(429, $response->getStatusCode());
    }

    public function testTransportFailuresFromTheInnerClientPropagateUnchanged(): void
    {
        $mock = new MockClient();
        $mock->addException(new class extends Exception implements ClientExceptionInterface {});
        $client = new ThrottlingClient($mock, sleeper: new FakeSleeper());

        $this->expectException(ClientExceptionInterface::class);

        $client->sendRequest(new Request('GET', 'https://example.test/'));
    }
}
