<?php

declare(strict_types=1);

namespace ApiFootball\DTO\TeamStatistics;

use ApiFootball\Internal\Scalars;

final readonly class FixturesStats
{
    public function __construct(
        public HomeAwayTotal $played,
        public HomeAwayTotal $wins,
        public HomeAwayTotal $draws,
        public HomeAwayTotal $loses,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            played: HomeAwayTotal::fromArray(Scalars::toMap($data['played'] ?? null)),
            wins: HomeAwayTotal::fromArray(Scalars::toMap($data['wins'] ?? null)),
            draws: HomeAwayTotal::fromArray(Scalars::toMap($data['draws'] ?? null)),
            loses: HomeAwayTotal::fromArray(Scalars::toMap($data['loses'] ?? null)),
        );
    }
}
