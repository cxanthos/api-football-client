<?php

declare(strict_types=1);

namespace ApiFootball\DTO\FixtureTeamPlayers;

use ApiFootball\Internal\Scalars;

/**
 * Distinct shape from `PlayerStatistics\Passes` — `accuracy` is a formatted percentage string ("68%")
 * here, not a plain int like in season statistics.
 */
final readonly class MatchPasses
{
    public function __construct(
        public ?int $total,
        public ?int $key,
        public ?string $accuracy,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: Scalars::toIntOrNull($data['total'] ?? null),
            key: Scalars::toIntOrNull($data['key'] ?? null),
            accuracy: Scalars::toStringOrNull($data['accuracy'] ?? null),
        );
    }
}
