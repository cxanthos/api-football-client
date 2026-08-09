<?php

declare(strict_types=1);

namespace ApiFootball\DTO\TeamStatistics;

use ApiFootball\DTO\HomeAwayPair;
use ApiFootball\Internal\Scalars;

final readonly class BiggestStats
{
    public function __construct(
        public BiggestStreak $streak,
        public BiggestScoreline $wins,
        public BiggestScoreline $loses,
        public HomeAwayPair $goalsFor,
        public HomeAwayPair $goalsAgainst,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $goals = Scalars::toMap($data['goals'] ?? null);

        return new self(
            streak: BiggestStreak::fromArray(Scalars::toMap($data['streak'] ?? null)),
            wins: BiggestScoreline::fromArray(Scalars::toMap($data['wins'] ?? null)),
            loses: BiggestScoreline::fromArray(Scalars::toMap($data['loses'] ?? null)),
            goalsFor: HomeAwayPair::fromArray(Scalars::toMap($goals['for'] ?? null)),
            goalsAgainst: HomeAwayPair::fromArray(Scalars::toMap($goals['against'] ?? null)),
        );
    }
}
