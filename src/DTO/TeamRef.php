<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/**
 * The minimal `{id, name, logo}` team reference embedded in many other endpoints' responses (fixtures
 * events, standings rows, transfers, coach records, player/team statistics, ...) — distinct from the full
 * `Team` DTO returned by `GET /teams` itself, which also carries venue/country/founded/national.
 */
final readonly class TeamRef
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $logo,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: Scalars::toInt($data['id'] ?? null),
            name: Scalars::toString($data['name'] ?? null),
            logo: Scalars::toStringOrNull($data['logo'] ?? null),
        );
    }
}
