<?php

declare(strict_types=1);

namespace ApiFootball\DTO\PlayerStatistics;

use ApiFootball\Internal\Scalars;

/** Distinct shape from `TeamStatistics\GoalsStats` (for/against split) — a player's own totals. */
final readonly class GoalsStats
{
    public function __construct(
        public ?int $total,
        public ?int $conceded,
        public ?int $assists,
        public ?int $saves,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: Scalars::toIntOrNull($data['total'] ?? null),
            conceded: Scalars::toIntOrNull($data['conceded'] ?? null),
            assists: Scalars::toIntOrNull($data['assists'] ?? null),
            saves: Scalars::toIntOrNull($data['saves'] ?? null),
        );
    }
}
