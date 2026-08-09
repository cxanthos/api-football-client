<?php

declare(strict_types=1);

namespace ApiFootball;

use ApiFootball\Http\Transport;
use ApiFootball\Resource\Account;
use ApiFootball\Resource\Coachs;
use ApiFootball\Resource\Countries;
use ApiFootball\Resource\Fixtures;
use ApiFootball\Resource\Leagues;
use ApiFootball\Resource\Players;
use ApiFootball\Resource\Standings;
use ApiFootball\Resource\Teams;
use ApiFootball\Resource\Transfers;
use ApiFootball\Resource\Trophies;
use ApiFootball\Resource\Venues;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Entry point. Exposes one getter per resource, each reusing the same Transport underneath (see
 * docs/design/endpoint-catalog.md for the full MVP resource list).
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
        ?LoggerInterface $logger = null,
    ) {
        $this->transport = new Transport(
            new Config($apiKey, $baseUri, $httpClient, $requestFactory, $uriFactory, $logger),
        );
    }

    public function countries(): Countries
    {
        return new Countries($this->transport);
    }

    public function leagues(): Leagues
    {
        return new Leagues($this->transport);
    }

    public function teams(): Teams
    {
        return new Teams($this->transport);
    }

    public function fixtures(): Fixtures
    {
        return new Fixtures($this->transport);
    }

    public function standings(): Standings
    {
        return new Standings($this->transport);
    }

    public function players(): Players
    {
        return new Players($this->transport);
    }

    public function coachs(): Coachs
    {
        return new Coachs($this->transport);
    }

    public function transfers(): Transfers
    {
        return new Transfers($this->transport);
    }

    public function trophies(): Trophies
    {
        return new Trophies($this->transport);
    }

    public function account(): Account
    {
        return new Account($this->transport);
    }

    public function venues(): Venues
    {
        return new Venues($this->transport);
    }

    /**
     * Rate-limit standing as of the most recent response, or null before the first call (FR-08).
     */
    public function rateLimit(): ?RateLimit
    {
        return $this->transport->lastRateLimit();
    }
}
