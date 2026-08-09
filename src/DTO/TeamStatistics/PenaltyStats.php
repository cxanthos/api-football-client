<?php

declare(strict_types=1);

namespace ApiFootball\DTO\TeamStatistics;

use ApiFootball\Internal\Scalars;

final readonly class PenaltyStats
{
    public function __construct(
        public PenaltyOutcome $scored,
        public PenaltyOutcome $missed,
        public ?int $total,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            scored: PenaltyOutcome::fromArray(Scalars::toMap($data['scored'] ?? null)),
            missed: PenaltyOutcome::fromArray(Scalars::toMap($data['missed'] ?? null)),
            total: Scalars::toIntOrNull($data['total'] ?? null),
        );
    }
}
