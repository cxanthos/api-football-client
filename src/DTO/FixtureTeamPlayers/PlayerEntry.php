<?php

declare(strict_types=1);

namespace ApiFootball\DTO\FixtureTeamPlayers;

use ApiFootball\DTO\PlayerRef;
use ApiFootball\Internal\Scalars;

/** One entry in a team's `players[]` for a fixture — a player plus their match statistics. */
final readonly class PlayerEntry
{
    /**
     * @param list<MatchStatEntry> $statistics
     */
    public function __construct(
        public PlayerRef $player,
        public array $statistics,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $statistics = Scalars::toArray($data['statistics'] ?? null);

        return new self(
            player: PlayerRef::fromArray(Scalars::toMap($data['player'] ?? null)),
            statistics: array_values(array_map(
                static fn(mixed $entry): MatchStatEntry => MatchStatEntry::fromArray(Scalars::toMap($entry)),
                $statistics,
            )),
        );
    }
}
