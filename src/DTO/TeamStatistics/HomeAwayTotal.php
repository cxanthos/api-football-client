<?php

declare(strict_types=1);

namespace ApiFootball\DTO\TeamStatistics;

use ApiFootball\Internal\Scalars;

/** Reused across fixtures.played/wins/draws/loses, clean_sheet, and failed_to_score. */
final readonly class HomeAwayTotal
{
    public function __construct(
        public ?int $home,
        public ?int $away,
        public ?int $total,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            home: Scalars::toIntOrNull($data['home'] ?? null),
            away: Scalars::toIntOrNull($data['away'] ?? null),
            total: Scalars::toIntOrNull($data['total'] ?? null),
        );
    }
}
