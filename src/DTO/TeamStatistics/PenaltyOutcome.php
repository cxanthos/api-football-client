<?php

declare(strict_types=1);

namespace ApiFootball\DTO\TeamStatistics;

use ApiFootball\Internal\Scalars;

final readonly class PenaltyOutcome
{
    public function __construct(
        public ?int $total,
        public ?string $percentage,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: Scalars::toIntOrNull($data['total'] ?? null),
            percentage: Scalars::toStringOrNull($data['percentage'] ?? null),
        );
    }
}
