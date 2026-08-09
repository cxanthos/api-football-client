<?php

declare(strict_types=1);

namespace ApiFootball\DTO\TeamStatistics;

use ApiFootball\Internal\Scalars;

/**
 * Goal averages per match — the API sends these as formatted decimal strings ("2.1"), not numbers, so the
 * DTO matches that faithfully rather than parsing to float.
 */
final readonly class HomeAwayAverage
{
    public function __construct(
        public ?string $home,
        public ?string $away,
        public ?string $total,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            home: Scalars::toStringOrNull($data['home'] ?? null),
            away: Scalars::toStringOrNull($data['away'] ?? null),
            total: Scalars::toStringOrNull($data['total'] ?? null),
        );
    }
}
