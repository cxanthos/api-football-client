<?php

declare(strict_types=1);

namespace ApiFootball;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * API key + transport wiring. No PSR-18 client or PSR-17 factories are required as direct dependencies of
 * this package (docs/design/sdk-design.md §4.7) — if you don't provide your own, `php-http/discovery`
 * finds whatever your project already has installed (Guzzle, Symfony HttpClient, nyholm/psr7, etc.).
 *
 * `logger` is optional and does nothing when unset (FR-11) — see `Http\Transport` for what actually gets
 * logged. The API key itself is never passed to the logger under any circumstance.
 */
final readonly class Config
{
    public ClientInterface $httpClient;

    public RequestFactoryInterface $requestFactory;

    public UriFactoryInterface $uriFactory;

    public function __construct(
        public string $apiKey,
        public string $baseUri = 'https://v3.football.api-sports.io',
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?UriFactoryInterface $uriFactory = null,
        public ?LoggerInterface $logger = null,
    ) {
        $this->httpClient = $httpClient ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->uriFactory = $uriFactory ?? Psr17FactoryDiscovery::findUriFactory();
    }
}
