<?php

declare(strict_types=1);

namespace ApiFootball\DTO\TeamStatistics;

use ApiFootball\Internal\Scalars;

/** One entry in `lineups[]` — a formation the team used and how many times. */
final readonly class LineupUsage
{
    public function __construct(
        public ?string $formation,
        public ?int $played,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            formation: Scalars::toStringOrNull($data['formation'] ?? null),
            played: Scalars::toIntOrNull($data['played'] ?? null),
        );
    }
}
