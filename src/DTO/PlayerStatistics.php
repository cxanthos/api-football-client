<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\DTO\PlayerStatistics\SeasonEntry;
use ApiFootball\Internal\Scalars;

/**
 * The response item shared by `/players`, `/players/topscorers`, `/players/topassists`,
 * `/players/topyellowcards`, `/players/topredcards` (docs/design/endpoint-catalog.md). Building blocks
 * live under `DTO\PlayerStatistics\*`.
 */
final readonly class PlayerStatistics
{
    /**
     * @param list<SeasonEntry> $statistics
     */
    public function __construct(
        public PlayerProfile $player,
        public array $statistics,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $statistics = Scalars::toArray($data['statistics'] ?? null);

        return new self(
            player: PlayerProfile::fromArray(Scalars::toMap($data['player'] ?? null)),
            statistics: array_values(array_map(
                static fn(mixed $entry): SeasonEntry => SeasonEntry::fromArray(Scalars::toMap($entry)),
                $statistics,
            )),
        );
    }
}
