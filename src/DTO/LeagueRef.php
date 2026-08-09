<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/**
 * The flat `{id, name, country, logo, flag, season, round?}` league reference embedded in fixtures,
 * team-statistics, and player-statistics responses — distinct from the full `League` DTO returned by
 * `GET /leagues` itself, which nests `country` as its own object plus a `seasons[]` array with coverage.
 * `round` is only present in fixture-family responses.
 */
final readonly class LeagueRef
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $country,
        public ?string $logo,
        public ?string $flag,
        public ?int $season,
        public ?string $round,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: Scalars::toInt($data['id'] ?? null),
            name: Scalars::toString($data['name'] ?? null),
            country: Scalars::toStringOrNull($data['country'] ?? null),
            logo: Scalars::toStringOrNull($data['logo'] ?? null),
            flag: Scalars::toStringOrNull($data['flag'] ?? null),
            season: Scalars::toIntOrNull($data['season'] ?? null),
            round: Scalars::toStringOrNull($data['round'] ?? null),
        );
    }
}
