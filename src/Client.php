<?php

declare(strict_types=1);

namespace ApiFootball;

use ApiFootball\Http\Transport;
use ApiFootball\Resource\Countries;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 * Entry point. Exposes one getter per resource — `countries()` is the only one so far; every later resource
 * from docs/design/endpoint-catalog.md just adds another getter here, reusing the same Transport.
 */
final readonly class Client
{
    private Transport $transport;

    public function __construct(
        string $apiKey,
        string $baseUri = 'https://v3.football.api-sports.io',
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?UriFactoryInterface $uriFactory = null,
    ) {
        $this->transport = new Transport(
            new Config($apiKey, $baseUri, $httpClient, $requestFactory, $uriFactory),
        );
    }

    public function countries(): Countries
    {
        return new Countries($this->transport);
    }

    /**
     * Rate-limit standing as of the most recent response, or null before the first call (FR-08).
     */
    public function rateLimit(): ?RateLimit
    {
        return $this->transport->lastRateLimit();
    }
}
