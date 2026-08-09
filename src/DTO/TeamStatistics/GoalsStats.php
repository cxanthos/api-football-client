<?php

declare(strict_types=1);

namespace ApiFootball\DTO\TeamStatistics;

use ApiFootball\Internal\Scalars;

final readonly class GoalsStats
{
    public function __construct(
        public GoalsSide $for,
        public GoalsSide $against,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            for: GoalsSide::fromArray(Scalars::toMap($data['for'] ?? null)),
            against: GoalsSide::fromArray(Scalars::toMap($data['against'] ?? null)),
        );
    }
}
