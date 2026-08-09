<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\DTO\TeamStatistics\BiggestStats;
use ApiFootball\DTO\TeamStatistics\CardsByMinute;
use ApiFootball\DTO\TeamStatistics\FixturesStats;
use ApiFootball\DTO\TeamStatistics\GoalsStats;
use ApiFootball\DTO\TeamStatistics\HomeAwayTotal;
use ApiFootball\DTO\TeamStatistics\LineupUsage;
use ApiFootball\DTO\TeamStatistics\PenaltyStats;
use ApiFootball\Internal\Scalars;

/**
 * `GET /teams/statistics` — the richest single-endpoint payload in the MVP tier (biggest win/loss, clean
 * sheets, formations used, goals/cards-by-minute buckets). See docs/design/endpoint-catalog.md. Building
 * blocks live under `DTO\TeamStatistics\*`.
 */
final readonly class TeamStatistics
{
    /**
     * @param list<LineupUsage> $lineups
     */
    public function __construct(
        public LeagueRef $league,
        public TeamRef $team,
        public ?string $form,
        public FixturesStats $fixtures,
        public GoalsStats $goals,
        public BiggestStats $biggest,
        public HomeAwayTotal $cleanSheet,
        public HomeAwayTotal $failedToScore,
        public PenaltyStats $penalty,
        public array $lineups,
        public CardsByMinute $cards,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $lineups = Scalars::toArray($data['lineups'] ?? null);

        return new self(
            league: LeagueRef::fromArray(Scalars::toMap($data['league'] ?? null)),
            team: TeamRef::fromArray(Scalars::toMap($data['team'] ?? null)),
            form: Scalars::toStringOrNull($data['form'] ?? null),
            fixtures: FixturesStats::fromArray(Scalars::toMap($data['fixtures'] ?? null)),
            goals: GoalsStats::fromArray(Scalars::toMap($data['goals'] ?? null)),
            biggest: BiggestStats::fromArray(Scalars::toMap($data['biggest'] ?? null)),
            cleanSheet: HomeAwayTotal::fromArray(Scalars::toMap($data['clean_sheet'] ?? null)),
            failedToScore: HomeAwayTotal::fromArray(Scalars::toMap($data['failed_to_score'] ?? null)),
            penalty: PenaltyStats::fromArray(Scalars::toMap($data['penalty'] ?? null)),
            lineups: array_values(array_map(
                static fn(mixed $item): LineupUsage => LineupUsage::fromArray(Scalars::toMap($item)),
                $lineups,
            )),
            cards: CardsByMinute::fromArray(Scalars::toMap($data['cards'] ?? null)),
        );
    }
}
