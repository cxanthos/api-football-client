<?php

declare(strict_types=1);

namespace ApiFootball\DTO\FixtureTeamPlayers;

use ApiFootball\DTO\PlayerStatistics\Dribbles;
use ApiFootball\DTO\PlayerStatistics\Duels;
use ApiFootball\DTO\PlayerStatistics\Fouls;
use ApiFootball\DTO\PlayerStatistics\GoalsStats;
use ApiFootball\DTO\PlayerStatistics\Penalty;
use ApiFootball\DTO\PlayerStatistics\Shots;
use ApiFootball\DTO\PlayerStatistics\Tackles;
use ApiFootball\Internal\Scalars;

/**
 * One entry in a player's match `statistics[]` (`GET /fixtures/players`). Reuses `Shots`, `GoalsStats`,
 * `Tackles`, `Duels`, `Dribbles`, `Fouls`, `Penalty` from `DTO\PlayerStatistics\*` — those shapes are
 * identical at match level and season level. `games`, `passes`, and `cards` are not — see `MatchGames`,
 * `MatchPasses`, `MatchCards`. `offsides` has no season-statistics equivalent at all.
 */
final readonly class MatchStatEntry
{
    public function __construct(
        public MatchGames $games,
        public ?int $offsides,
        public Shots $shots,
        public GoalsStats $goals,
        public MatchPasses $passes,
        public Tackles $tackles,
        public Duels $duels,
        public Dribbles $dribbles,
        public Fouls $fouls,
        public MatchCards $cards,
        public Penalty $penalty,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            games: MatchGames::fromArray(Scalars::toMap($data['games'] ?? null)),
            offsides: Scalars::toIntOrNull($data['offsides'] ?? null),
            shots: Shots::fromArray(Scalars::toMap($data['shots'] ?? null)),
            goals: GoalsStats::fromArray(Scalars::toMap($data['goals'] ?? null)),
            passes: MatchPasses::fromArray(Scalars::toMap($data['passes'] ?? null)),
            tackles: Tackles::fromArray(Scalars::toMap($data['tackles'] ?? null)),
            duels: Duels::fromArray(Scalars::toMap($data['duels'] ?? null)),
            dribbles: Dribbles::fromArray(Scalars::toMap($data['dribbles'] ?? null)),
            fouls: Fouls::fromArray(Scalars::toMap($data['fouls'] ?? null)),
            cards: MatchCards::fromArray(Scalars::toMap($data['cards'] ?? null)),
            penalty: Penalty::fromArray(Scalars::toMap($data['penalty'] ?? null)),
        );
    }
}
