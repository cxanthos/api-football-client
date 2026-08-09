<?php

declare(strict_types=1);

namespace ApiFootball\Http;

use ApiFootball\Config;
use ApiFootball\Envelope;
use ApiFootball\Exception\TransportException;
use ApiFootball\RateLimit;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * Builds and sends the actual HTTP request for every resource call, and is the one place that decides
 * transport failure (throws) vs. anything else (doesn't). Shared by every resource — see
 * Resource\AbstractResource.
 *
 * Remembers the most recent response's rate-limit headers (FR-08: every successful exchange must expose
 * them). This is the one deliberately mutable/stateful piece in an otherwise readonly-everywhere codebase —
 * rate-limit standing is a property of the connection over time, not of any single typed result, so it
 * doesn't belong bolted onto Result.
 */
final class Transport
{
    private ?RateLimit $lastRateLimit = null;

    public function __construct(
        private readonly Config $config,
    ) {}

    /**
     * @param array<string, int|string> $query
     *
     * @throws TransportException on any HTTP/network failure or non-JSON response body
     */
    public function get(string $path, array $query = []): Envelope
    {
        $uri = $this->config->uriFactory->createUri($this->config->baseUri . $path);

        if ($query !== []) {
            $uri = $uri->withQuery(http_build_query($query));
        }

        $request = $this->config->requestFactory
            ->createRequest('GET', $uri)
            ->withHeader('x-apisports-key', $this->config->apiKey);

        try {
            $response = $this->config->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            throw new TransportException(
                'HTTP request to API-Football failed: ' . $exception->getMessage(),
                previous: $exception,
            );
        }

        $this->lastRateLimit = RateLimit::fromHeaders($response);

        try {
            return Envelope::fromJson((string) $response->getBody());
        } catch (JsonException $exception) {
            throw new TransportException(
                'API-Football returned a non-JSON response body.',
                previous: $exception,
            );
        }
    }

    public function lastRateLimit(): ?RateLimit
    {
        return $this->lastRateLimit;
    }
}
