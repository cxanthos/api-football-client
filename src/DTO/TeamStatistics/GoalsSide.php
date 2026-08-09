<?php

declare(strict_types=1);

namespace ApiFootball\DTO\TeamStatistics;

use ApiFootball\Internal\Scalars;

/** One side (`for` or `against`) of the `goals` block. */
final readonly class GoalsSide
{
    /**
     * @param array<string,MinuteBucket> $minute
     * @param array<string,UnderOverBucket> $underOver
     */
    public function __construct(
        public HomeAwayTotal $total,
        public HomeAwayAverage $average,
        public array $minute,
        public array $underOver,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: HomeAwayTotal::fromArray(Scalars::toMap($data['total'] ?? null)),
            average: HomeAwayAverage::fromArray(Scalars::toMap($data['average'] ?? null)),
            minute: MinuteBucket::mapFromArray(Scalars::toMap($data['minute'] ?? null)),
            underOver: UnderOverBucket::mapFromArray(Scalars::toMap($data['under_over'] ?? null)),
        );
    }
}
