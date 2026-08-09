<?php

declare(strict_types=1);

namespace ApiFootball\DTO\FixtureTeamPlayers;

use ApiFootball\Internal\Scalars;

/** Distinct shape from `PlayerStatistics\Cards` — no `yellowred` field at match level. */
final readonly class MatchCards
{
    public function __construct(
        public ?int $yellow,
        public ?int $red,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            yellow: Scalars::toIntOrNull($data['yellow'] ?? null),
            red: Scalars::toIntOrNull($data['red'] ?? null),
        );
    }
}
