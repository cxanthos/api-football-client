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
 *
 * Also the single chokepoint for the optional PSR-3 logging (FR-11) — logging requests, errors, and
 * rate-limit warnings here means no resource class needs any awareness of logging at all. Only the
 * request method + URI are ever logged, never headers — the API key is a header
 * (`x-apisports-key`), so it can never end up in a log line by construction, not by careful omission.
 */
final class Transport
{
    private const int LOW_DAILY_REMAINING_WARNING_THRESHOLD = 10;

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

        $this->config->logger?->debug('API-Football request', [
            'method' => 'GET',
            'uri' => (string) $uri,
        ]);

        try {
            $response = $this->config->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            $this->config->logger?->error('API-Football transport failure', [
                'uri' => (string) $uri,
                'exception' => $exception->getMessage(),
            ]);

            throw new TransportException(
                'HTTP request to API-Football failed: ' . $exception->getMessage(),
                previous: $exception,
            );
        }

        $this->lastRateLimit = RateLimit::fromHeaders($response);
        $this->logRateLimitWarningsIfLow();

        try {
            $envelope = Envelope::fromJson((string) $response->getBody());
        } catch (JsonException $exception) {
            $this->config->logger?->error('API-Football returned a non-JSON response body', [
                'uri' => (string) $uri,
                'status' => $response->getStatusCode(),
            ]);

            throw new TransportException(
                'API-Football returned a non-JSON response body.',
                previous: $exception,
            );
        }

        if ($envelope->hasErrors()) {
            $this->config->logger?->warning('API-Football returned API-level errors', [
                'endpoint' => $envelope->endpoint,
                'errors' => $envelope->errors,
            ]);
        } else {
            $this->config->logger?->debug('API-Football response', [
                'endpoint' => $envelope->endpoint,
                'results' => $envelope->results,
                'status' => $response->getStatusCode(),
            ]);
        }

        return $envelope;
    }

    public function lastRateLimit(): ?RateLimit
    {
        return $this->lastRateLimit;
    }

    private function logRateLimitWarningsIfLow(): void
    {
        $logger = $this->config->logger;
        $rateLimit = $this->lastRateLimit;

        if ($logger === null || $rateLimit === null) {
            return;
        }

        if ($rateLimit->dailyRemaining !== null && $rateLimit->dailyRemaining <= self::LOW_DAILY_REMAINING_WARNING_THRESHOLD) {
            $logger->warning('API-Football daily quota running low', [
                'dailyRemaining' => $rateLimit->dailyRemaining,
                'dailyLimit' => $rateLimit->dailyLimit,
            ]);
        }

        if ($rateLimit->perMinuteRemaining !== null && $rateLimit->perMinuteRemaining <= 0) {
            $logger->warning('API-Football per-minute rate limit exhausted', [
                'perMinuteLimit' => $rateLimit->perMinuteLimit,
            ]);
        }
    }
}
