<?php

declare(strict_types=1);

namespace ApiFootball\Tests\Http;

use ApiFootball\Config;
use ApiFootball\ErrorId;
use ApiFootball\Http\Transport;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * Non-logging Transport plumbing — see TransportLoggingTest for the PSR-3 logging behavior sharing the
 * same harness.
 */
final class TransportTest extends TestCase
{
    public function testEnvelopeCarriesRateLimitedErrorIdOnHttp429(): void
    {
        $transport = $this->buildTransport(429, $this->envelopeJson(['errors' => ['rateLimit' => 'x']]));

        $envelope = $transport->get('/countries');

        self::assertSame(ErrorId::RateLimited, $envelope->errorId);
    }

    public function testEnvelopeHasNoErrorIdOnHttp200WithApiLevelErrors(): void
    {
        $transport = $this->buildTransport(200, $this->envelopeJson(['errors' => ['season' => 'bad']]));

        $envelope = $transport->get('/countries');

        self::assertNull($envelope->errorId);
    }

    public function testEnvelopeHasNoErrorIdOnPlainSuccess(): void
    {
        $transport = $this->buildTransport(200, $this->envelopeJson());

        $envelope = $transport->get('/countries');

        self::assertNull($envelope->errorId);
    }

    private function buildTransport(int $status, string $body): Transport
    {
        $mock = new MockClient();
        $psr17 = new Psr17Factory();
        $mock->addResponse(new Response($status, [], $body));

        return new Transport(new Config(
            apiKey: 'test-key',
            httpClient: $mock,
            requestFactory: $psr17,
            uriFactory: $psr17,
        ));
    }

    /**
     * @param array<string,mixed> $overrides
     */
    private function envelopeJson(array $overrides = []): string
    {
        $envelope = array_merge([
            'get' => 'countries',
            'parameters' => [],
            'errors' => [],
            'results' => 0,
            'response' => [],
        ], $overrides);

        return (string) json_encode($envelope, JSON_THROW_ON_ERROR);
    }
}
