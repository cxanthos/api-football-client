<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/**
 * `GET /trophies` response item — an honour for a player or coach. `season` is a free-text string here
 * (e.g. `"Peru 2011"`), not the 4-digit year convention used elsewhere in the API.
 */
final readonly class Trophy
{
    public function __construct(
        public ?string $league,
        public ?string $country,
        public ?string $season,
        public ?string $place,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            league: Scalars::toStringOrNull($data['league'] ?? null),
            country: Scalars::toStringOrNull($data['country'] ?? null),
            season: Scalars::toStringOrNull($data['season'] ?? null),
            place: Scalars::toStringOrNull($data['place'] ?? null),
        );
    }
}
