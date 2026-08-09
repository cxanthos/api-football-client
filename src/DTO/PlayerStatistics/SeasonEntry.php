<?php

declare(strict_types=1);

namespace ApiFootball\DTO\PlayerStatistics;

use ApiFootball\DTO\LeagueRef;
use ApiFootball\DTO\TeamRef;
use ApiFootball\Internal\Scalars;

/**
 * One entry in a player's `statistics[]` — their record for one team+league+season combination. A player
 * can have more than one entry per season if they transferred mid-season (docs/design/endpoint-catalog.md).
 */
final readonly class SeasonEntry
{
    public function __construct(
        public TeamRef $team,
        public LeagueRef $league,
        public Games $games,
        public Substitutes $substitutes,
        public Shots $shots,
        public GoalsStats $goals,
        public Passes $passes,
        public Tackles $tackles,
        public Duels $duels,
        public Dribbles $dribbles,
        public Fouls $fouls,
        public Cards $cards,
        public Penalty $penalty,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            team: TeamRef::fromArray(Scalars::toMap($data['team'] ?? null)),
            league: LeagueRef::fromArray(Scalars::toMap($data['league'] ?? null)),
            games: Games::fromArray(Scalars::toMap($data['games'] ?? null)),
            substitutes: Substitutes::fromArray(Scalars::toMap($data['substitutes'] ?? null)),
            shots: Shots::fromArray(Scalars::toMap($data['shots'] ?? null)),
            goals: GoalsStats::fromArray(Scalars::toMap($data['goals'] ?? null)),
            passes: Passes::fromArray(Scalars::toMap($data['passes'] ?? null)),
            tackles: Tackles::fromArray(Scalars::toMap($data['tackles'] ?? null)),
            duels: Duels::fromArray(Scalars::toMap($data['duels'] ?? null)),
            dribbles: Dribbles::fromArray(Scalars::toMap($data['dribbles'] ?? null)),
            fouls: Fouls::fromArray(Scalars::toMap($data['fouls'] ?? null)),
            cards: Cards::fromArray(Scalars::toMap($data['cards'] ?? null)),
            penalty: Penalty::fromArray(Scalars::toMap($data['penalty'] ?? null)),
        );
    }
}
