<?php

declare(strict_types=1);

namespace ApiFootball\Tests;

use ApiFootball\Client;
use Exception;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * Shared plumbing for every resource test: a Client wired to a php-http/mock-client double, either
 * queued with a canned JSON response or set up to fail at the transport layer. See
 * tests/Resource/CountriesTest.php for the original three-case pattern (success / API-error / transport
 * failure) every resource test follows.
 */
abstract class ResourceTestCase extends TestCase
{
    /**
     * @param array<string,string> $headers
     */
    protected function clientWithResponse(string $jsonBody, int $status = 200, array $headers = []): Client
    {
        $mockClient = new MockClient();
        $psr17 = new Psr17Factory();

        $mockClient->addResponse(new Response(
            status: $status,
            headers: [...['Content-Type' => 'application/json'], ...$headers],
            body: $jsonBody,
        ));

        return new Client(
            apiKey: 'test-key',
            httpClient: $mockClient,
            requestFactory: $psr17,
            uriFactory: $psr17,
        );
    }

    protected function clientWithTransportFailure(): Client
    {
        $mockClient = new MockClient();
        $psr17 = new Psr17Factory();

        $mockClient->addException(new class extends Exception implements ClientExceptionInterface {});

        return new Client(
            apiKey: 'test-key',
            httpClient: $mockClient,
            requestFactory: $psr17,
            uriFactory: $psr17,
        );
    }

    /**
     * @param array<string,mixed> $overrides
     */
    protected function envelopeJson(array $overrides = []): string
    {
        $envelope = array_merge([
            'get' => '',
            'parameters' => [],
            'errors' => [],
            'results' => 0,
            'response' => [],
        ], $overrides);

        return (string) json_encode($envelope, JSON_THROW_ON_ERROR);
    }
}
