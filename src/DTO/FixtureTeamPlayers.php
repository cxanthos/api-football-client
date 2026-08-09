<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\DTO\FixtureTeamPlayers\PlayerEntry;
use ApiFootball\Internal\Scalars;

/**
 * `GET /fixtures/players` response item — one team's full per-player match statistics for a fixture
 * (Tier 2, the richest and most expensive per-match endpoint). `team.update` is a sibling timestamp field
 * the API embeds inside the team object, not part of `TeamRef` — kept as its own property here instead.
 */
final readonly class FixtureTeamPlayers
{
    /**
     * @param list<PlayerEntry> $players
     */
    public function __construct(
        public TeamRef $team,
        public ?string $updatedAt,
        public array $players,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $team = Scalars::toMap($data['team'] ?? null);
        $players = Scalars::toArray($data['players'] ?? null);

        return new self(
            team: TeamRef::fromArray($team),
            updatedAt: Scalars::toStringOrNull($team['update'] ?? null),
            players: array_values(array_map(
                static fn(mixed $entry): PlayerEntry => PlayerEntry::fromArray(Scalars::toMap($entry)),
                $players,
            )),
        );
    }
}
